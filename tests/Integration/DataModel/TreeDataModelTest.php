<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Integration\DataModel;

use verfriemelt\wrapped\_\Database\Driver\SQLite;
use verfriemelt\wrapped\_\DataModel\TreeDataModel;
use Override;
use verfriemelt\wrapped\Tests\Integration\DatabaseTestCase;

class TreeDummy extends TreeDataModel
{
    public string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }
}

class TreeDataModelTest extends DatabaseTestCase
{
    #[Override]
    public function setUp(): void
    {
        if (static::$connection instanceof SQLite) {
            static::markTestSkipped('sqlite not supported');
            return;
        }

        $this->tearDown();
        static::$connection->query('drop table if exists "TreeDummy";');
        static::$connection->query(
            'create table "TreeDummy" ( id serial primary key, name text, "left" int, "right" int, parent_id int, depth int );',
        );
    }

    #[Override]
    public function tearDown(): void
    {
        static::$connection->query('drop table if exists "TreeDummy";');
    }

    public function test_save_under(): void
    {
        $parent = new TreeDummy();
        $parent->setName('parent');
        $parent->save();

        $child = new TreeDummy();
        $child->under($parent);
        $child->setName('child');
        $child->save();

        $parent->reload();

        static::assertSame(1, $parent->getId(), 'id');
        static::assertSame(1, $parent->getLeft(), 'parent left');
        static::assertSame(4, $parent->getRight(), 'right');
        static::assertSame(0, $parent->getDepth(), 'depth');
        static::assertSame('parent', $parent->getName(), 'name');
        static::assertSame(null, $parent->getParentId(), 'parent');

        static::assertSame(2, $child->getId(), 'id');
        static::assertSame(2, $child->getLeft(), 'left');
        static::assertSame(3, $child->getRight(), 'right');
        static::assertSame(1, $child->getDepth(), 'depth');
        static::assertSame('child', $child->getName(), 'name');
        static::assertSame(1, $child->getParentId(), 'parent');

        $child2 = new TreeDummy();
        $child2->under($parent);
        $child2->setName('2nd child');
        $child2->save();

        $parent->reload();
        $child->reload();

        static::assertSame(1, $parent->getLeft(), 'left');
        static::assertSame(6, $parent->getRight(), 'right');

        static::assertSame(2, $child->getLeft(), 'left');
        static::assertSame(3, $child->getRight(), 'right');

        static::assertSame(4, $child2->getLeft(), 'left');
        static::assertSame(5, $child2->getRight(), 'right');

        $child3 = new TreeDummy();
        $child3->under($child2);
        $child3->setName('3nd child');
        $child3->save();

        $parent->reload();
        $child->reload();
        $child2->reload();

        static::assertSame(1, $parent->getLeft(), 'left');
        static::assertSame(8, $parent->getRight(), 'right');

        static::assertSame(2, $child->getLeft(), 'left');
        static::assertSame(3, $child->getRight(), 'right');

        static::assertSame(4, $child2->getLeft(), 'left');
        static::assertSame(7, $child2->getRight(), 'right');

        static::assertSame(5, $child3->getLeft(), 'left');
        static::assertSame(6, $child3->getRight(), 'right');
    }

    public function test_update(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);

        // update
        $test = TreeDummy::findSingle(['name' => 'b']);
        $test->setName('update')->save();

        $this->validateStruct([
            'a' => [],
            'update' => [],
            'c' => [],
        ]);
    }

    protected function createStructure($struct, $parent = null): void
    {
        foreach ($struct as $e => $s) {
            $i = (new TreeDummy())->setName($e);

            if ($parent) {
                $i->under($parent);
            }

            $i->save();
            $i->reload();

            $this->createStructure($s, $i);
        }
    }

    protected function validateStruct($struct, &$count = 0, $depth = 0, $parentId = null): void
    {
        foreach ($struct as $e => $s) {
            $instance = TreeDummy::findSingle(['name' => $e]);

            static::assertSame($depth, $instance->getDepth(), $e . ' depth');
            static::assertSame($parentId, $instance->getParentId(), $e . ' parentid');

            static::assertSame(++$count, $instance->getLeft(), $e . ' left');

            $this->validateStruct($s, $count, $depth + 1, $instance->getId());

            static::assertSame(++$count, $instance->getRight(), $e . ' right');
        }
    }

    protected function getStruct($struct, &$res = []): array
    {
        foreach ($struct as $e => $s) {
            $res[] = TreeDummy::findSingle(['name' => $e]);
            $this->getStruct($s, $res);
        }

        return $res;
    }

    public function test_simple_save(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);
        $this->validateStruct($struct);
    }

    public function test_move_simple_under_left(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);
        [$a, $b, $c] = $this->getStruct($struct);

        $c->move()->under($b, false);
        $c->save();

        $this->validateStruct([
            'a' => [],
            'b' => [
                'c' => [],
            ],
        ]);
    }

    public function test_move_simple_under_right(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);

        $c = TreeDummy::findSingle(['name' => 'c']);
        $c->move()->under(TreeDummy::findSingle(['name' => 'b']), true);
        $c->save();

        $this->validateStruct([
            'a' => [],
            'b' => [
                'c' => [],
            ],
        ]);
    }

    public function test_move_under_left(): void
    {
        $struct = [
            'a' => [],
            'b' => [
                'e' => [],
                'f' => [],
            ],
            'c' => [],
        ];

        $this->createStructure($struct);

        $c = TreeDummy::findSingle(['name' => 'c']);
        $c->move()->under(TreeDummy::findSingle(['name' => 'b']), false);
        $c->save();

        $this->validateStruct([
            'a' => [],
            'b' => [
                'c' => [],
                'e' => [],
                'f' => [],
            ],
        ]);
    }

    public function test_move_under_right(): void
    {
        $struct = [
            'a' => [],
            'b' => [
                'e' => [],
                'f' => [],
            ],
            'c' => [],
        ];

        $this->createStructure($struct);

        $c = TreeDummy::findSingle(['name' => 'c']);
        $c->move()->under(TreeDummy::findSingle(['name' => 'b']), true);
        $c->save();

        $this->validateStruct([
            'a' => [],
            'b' => [
                'e' => [],
                'f' => [],
                'c' => [],
            ],
        ]);
    }

    public function test_move(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);
        [$a, $b, $c] = $this->getStruct($struct);

        $c->move()->under($b);
        $c->save();

        $this->validateStruct([
            'a' => [],
            'b' => [
                'c' => [],
            ],
        ]);

        $c->move()->under($a)->save();
        $this->validateStruct([
            'a' => [
                'c' => [],
            ],
            'b' => [],
        ]);

        $a->move()->under($b)->save();
        $this->validateStruct([
            'b' => [
                'a' => [
                    'c' => [],
                ],
            ],
        ]);

        $d = (new TreeDummy())->setName('d')->save();

        $d->move()->under($c)->save();
        $this->validateStruct([
            'b' => [
                'a' => [
                    'c' => [
                        'd' => [],
                    ],
                ],
            ],
        ]);

        $d->move()->under($a)->save();
        $this->validateStruct([
            'b' => [
                'a' => [
                    'd' => [],
                    'c' => [],
                ],
            ],
        ]);

        $d->move()->after($b)->save();
        $this->validateStruct([
            'b' => [
                'a' => [
                    'c' => [],
                ],
            ],
            'd' => [],
        ]);
    }

    public function test_deeply_nested_move(): void
    {
        $struct = [
            'a' => [
                'b' => [
                    'c' => [],
                    'd' => [],
                ],
            ],
            'e' => [
                'f' => [
                    'g' => [],
                    'h' => [],
                ],
            ],
        ];

        $this->createStructure($struct);

        TreeDummy::findSingle(['name' => 'b'])->under(TreeDummy::findSingle(['name' => 'e']))->save();

        $struct = [
            'a' => [],
            'e' => [
                'f' => [
                    'g' => [],
                    'h' => [],
                ],
                'b' => [
                    'c' => [],
                    'd' => [],
                ],
            ],
        ];

        $this->validateStruct($struct);

        TreeDummy::findSingle(['name' => 'c'])->under(TreeDummy::findSingle(['name' => 'a']))->save();

        $struct = [
            'a' => [
                'c' => [],
            ],
            'e' => [
                'f' => [
                    'g' => [],
                    'h' => [],
                ],
                'b' => [
                    'd' => [],
                ],
            ],
        ];

        $this->validateStruct($struct);

        TreeDummy::findSingle(['name' => 'a'])->under(TreeDummy::findSingle(['name' => 'h']))->save();

        $struct = [
            'e' => [
                'f' => [
                    'g' => [],
                    'h' => [
                        'a' => [
                            'c' => [],
                        ],
                    ],
                ],
                'b' => [
                    'd' => [],
                ],
            ],
        ];

        $this->validateStruct($struct);
    }

    public function test_positioned_move(): void
    {
        $struct = [
            'a' => [
                'a1' => [],
                'a2' => [],
                'a3' => [],
            ],
            'b' => [],
            'c' => [],
            'd' => [],
        ];

        $this->createStructure($struct);

        $move = TreeDummy::findSingle(['name' => 'c']);
        $move->move()->after(TreeDummy::findSingle(['name' => 'a2']))->save();

        $this->validateStruct([
            'a' => [
                'a1' => [],
                'a2' => [],
                'c' => [],
                'a3' => [],
            ],
            'b' => [],
            'd' => [],
        ]);

        $move = TreeDummy::findSingle(['name' => 'c']);
        $move->move()->before(TreeDummy::findSingle(['name' => 'a2']))->save();

        $this->validateStruct([
            'a' => [
                'a1' => [],
                'c' => [],
                'a2' => [],
                'a3' => [],
            ],
            'b' => [],
            'd' => [],
        ]);

        $move = TreeDummy::findSingle(['name' => 'b']);

        // at start
        $move->move()->under(TreeDummy::findSingle(['name' => 'a']), false)->save();

        $this->validateStruct([
            'a' => [
                'b' => [],
                'a1' => [],
                'c' => [],
                'a2' => [],
                'a3' => [],
            ],
            'd' => [],
        ]);

        $move = TreeDummy::findSingle(['name' => 'd']);

        // at end
        $move->move()->under(TreeDummy::findSingle(['name' => 'a']), true)->save();

        $this->validateStruct([
            'a' => [
                'b' => [],
                'a1' => [],
                'c' => [],
                'a2' => [],
                'a3' => [],
                'd' => [],
            ],
        ]);
    }

    public function test_insert(): void
    {
        $struct = [
            'a' => [],
            'b' => [],
            'c' => [],
        ];

        $this->createStructure($struct);

        $instance = new TreeDummy();
        $instance->setName('i');
        $instance->save();

        $this->validateStruct(
            [
                'a' => [],
                'b' => [],
                'c' => [],
                'i' => [],
            ],
        );

        $instance = new TreeDummy();
        $instance->setName('i2');
        $instance->after(TreeDummy::findSingle(['name' => 'a']));
        $instance->save();

        $this->validateStruct(
            [
                'a' => [],
                'i2' => [],
                'b' => [],
                'c' => [],
                'i' => [],
            ],
        );
    }
}
