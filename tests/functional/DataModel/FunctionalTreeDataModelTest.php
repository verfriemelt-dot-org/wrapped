<?php

    namespace functional;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\DataModel\TreeDataModel;

    class TreeDummy
    extends TreeDataModel {

        public ?int $id = null;

        public ?string $name = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function getName(): ?string {
            return $this->name;
        }

        public function setName( ?string $name ) {
            $this->name = $name;
            return $this;
        }

    }

    class FunctionalTreeDataModelTest
    extends TestCase {

        static $connection;

        public static function setUpBeforeClass(): void {
            static::$connection = Database::createNewConnection( 'default', Postgres::class, "docker", "docker", "localhost", "docker", 5432 );
        }

        public function setUp(): void {
            static::$connection->query( "set log_statement = 'all'" );
            $this->tearDown();
            static::$connection->query( 'drop table if exists "TreeDummy";' );
            static::$connection->query( 'create table "TreeDummy" ( id serial primary key, name text, "left" int, "right" int, parent_id int, depth int );' );
        }

        public function tearDown(): void {
//            static::$connection->query( 'drop table if exists "TreeDummy";' );
        }

        public function test() {
            new TreeDummy;
        }

        public function testSaveUnder() {

            $parent = new TreeDummy;
            $parent->setName( 'parent' );
            $parent->save();

            $child = new TreeDummy;
            $child->under( $parent );
            $child->setName( 'child' );
            $child->save();

            $parent->reload();

            $this->assertSame( 1, $parent->getId(), 'id' );
            $this->assertSame( 1, $parent->getLeft(), 'parent left' );
            $this->assertSame( 4, $parent->getRight(), 'right' );
            $this->assertSame( 0, $parent->getDepth(), 'depth' );
            $this->assertSame( 'parent', $parent->getName(), 'name' );
            $this->assertSame( null, $parent->getParentId(), 'parent' );

            $this->assertSame( 2, $child->getId(), 'id' );
            $this->assertSame( 2, $child->getLeft(), 'left' );
            $this->assertSame( 3, $child->getRight(), 'right' );
            $this->assertSame( 1, $child->getDepth(), 'depth' );
            $this->assertSame( 'child', $child->getName(), 'name' );
            $this->assertSame( 1, $child->getParentId(), 'parent' );

            $child2 = new TreeDummy;
            $child2->under( $parent );
            $child2->setName( '2nd child' );
            $child2->save();


            $parent->reload();
            $child->reload();

//            codecept_debug( $parent );

            $this->assertSame( 1, $parent->getLeft(), 'left' );
            $this->assertSame( 6, $parent->getRight(), 'right' );

            $this->assertSame( 2, $child->getLeft(), 'left' );
            $this->assertSame( 3, $child->getRight(), 'right' );

            $this->assertSame( 4, $child2->getLeft(), 'left' );
            $this->assertSame( 5, $child2->getRight(), 'right' );

            $child3 = new TreeDummy;
            $child3->under( $child2 );
            $child3->setName( '3nd child' );
            $child3->save();

            $parent->reload();
            $child->reload();
            $child2->reload();

            $this->assertSame( 1, $parent->getLeft(), 'left' );
            $this->assertSame( 8, $parent->getRight(), 'right' );

            $this->assertSame( 2, $child->getLeft(), 'left' );
            $this->assertSame( 3, $child->getRight(), 'right' );

            $this->assertSame( 4, $child2->getLeft(), 'left' );
            $this->assertSame( 7, $child2->getRight(), 'right' );

            $this->assertSame( 5, $child3->getLeft(), 'left' );
            $this->assertSame( 6, $child3->getRight(), 'right' );
        }

        public function testUpdate() {

            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );

            // update
            $test = TreeDummy::findSingle( [ 'name' => 'b' ] );
            $test->setName( 'update' )->save();

            $this->validateStruct( [
                "a"      => [],
                "update" => [],
                "c"      => [],
            ] );
        }

        public function createStructure( $struct, $parent = null ) {

            foreach ( $struct as $e => $s ) {

                $i = (new TreeDummy() )->setName( $e );

                if ( $parent ) {
                    $i->under( $parent );
                }

                $i->save();
                $i->reload();

                $this->createStructure( $s, $i );
            }
        }

        public function validateStruct( $struct, &$count = 0, $depth = 0, $parentId = null ) {


            foreach ( $struct as $e => $s ) {

                $instance = TreeDummy::findSingle( [ 'name' => $e ] );

                $this->assertSame( $depth, $instance->getDepth(), $e . ' depth' );
                $this->assertSame( $parentId, $instance->getParentId(), $e . ' parentid' );

                $this->assertSame( ++$count, $instance->getLeft(), $e . ' left' );

                $this->validateStruct( $s, $count, $depth + 1, $instance->getId() );

                $this->assertSame( ++$count, $instance->getRight(), $e . ' right' );
            }
        }

        public function getStruct( $struct, &$res = [] ) {
            foreach ( $struct as $e => $s ) {
                $res[] = TreeDummy::findSingle( [ 'name' => $e ] );
                $this->getStruct( $s, $res );
            }

            return $res;
        }

        public function testSimpleSave() {
            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );
            $this->validateStruct( $struct );
        }

        public function testMoveSimpleUnderLeft() {

            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );
            [$a, $b, $c] = $this->getStruct( $struct );

            $c->move()->under( $b, false );
            $c->save();

            $this->validateStruct( [
                "a" => [],
                "b" => [
                    "c" => [],
                ],
            ] );
        }

        public function testMoveSimpleUnderRight() {

            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );

            $c = TreeDummy::findSingle( [ 'name' => 'c' ] );
            $c->move()->under( TreeDummy::findSingle( [ 'name' => 'b' ] ), true );
            $c->save();

            $this->validateStruct( [
                "a" => [],
                "b" => [
                    "c" => [],
                ],
            ] );
        }

        public function testMoveUnderLeft() {

            $struct = [
                "a" => [],
                "b" => [
                    "e" => [],
                    "f" => [],
                ],
                "c" => [],
            ];

            $this->createStructure( $struct );

            $c = TreeDummy::findSingle( [ 'name' => 'c' ] );
            $c->move()->under( TreeDummy::findSingle( [ 'name' => 'b' ] ), false );
            $c->save();

            $this->validateStruct( [
                "a" => [],
                "b" => [
                    "c" => [],
                    "e" => [],
                    "f" => [],
                ],
            ] );
        }

        public function testMoveUnderRight() {

            $struct = [
                "a" => [],
                "b" => [
                    "e" => [],
                    "f" => [],
                ],
                "c" => [],
            ];

            $this->createStructure( $struct );

            $c = TreeDummy::findSingle( [ 'name' => 'c' ] );
            $c->move()->under( TreeDummy::findSingle( [ 'name' => 'b' ] ), true );
            $c->save();

            $this->validateStruct( [
                "a" => [],
                "b" => [
                    "e" => [],
                    "f" => [],
                    "c" => [],
                ],
            ] );
        }

        public function testMove() {

            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );
            [$a, $b, $c] = $this->getStruct( $struct );

            $c->move()->under( $b );
            $c->save();

            $this->validateStruct( [
                "a" => [],
                "b" => [
                    "c" => [],
                ],
            ] );

            return;

            $c->move()->under( $a )->save();
            $this->validateStruct( [
                "a" => [
                    "c" => [],
                ],
                "b" => [],
            ] );

            $a->move()->under( $b )->save();
            $this->validateStruct( [
                "b" => [
                    "a" => [
                        "c" => [],
                    ]
                ],
            ] );

            $d = (new TreeDummy() )->setName( "d" )->save();

            $d->move()->under( $c )->save();
            $this->validateStruct( [
                "b" => [
                    "a" => [
                        "c" => [
                            "d" => []
                        ],
                    ]
                ],
            ] );

            $d->move()->under( $a )->save();
            $this->validateStruct( [
                "b" => [
                    "a" => [
                        "d" => [],
                        "c" => [],
                    ]
                ],
            ] );

            $d->move()->after( $b )->save();
            $this->validateStruct( [
                "b" => [
                    "a" => [
                        "c" => [],
                    ]
                ],
                "d" => [],
            ] );
        }

        public function testDeeplyNestedMove() {


            $struct = [
                "a" => [
                    "b" => [
                        "c" => [],
                        "d" => []
                    ],
                ],
                "e" => [
                    "f" => [
                        "g" => [],
                        "h" => []
                    ],
                ],
            ];

            $this->createStructure( $struct );

            TreeDummy::findSingle( [ 'name' => 'b' ] )->under( TreeDummy::findSingle( [ 'name' => 'e' ] ) )->save();

            $struct = [
                "a" => [],
                "e" => [
                    "f" => [
                        "g" => [],
                        "h" => []
                    ],
                    "b" => [
                        "c" => [],
                        "d" => []
                    ],
                ],
            ];

            $this->validateStruct( $struct );

            TreeDummy::findSingle( [ 'name' => 'c' ] )->under( TreeDummy::findSingle( [ 'name' => 'a' ] ) )->save();

            $struct = [
                "a" => [
                    "c" => [],
                ],
                "e" => [
                    "f" => [
                        "g" => [],
                        "h" => []
                    ],
                    "b" => [
                        "d" => []
                    ],
                ],
            ];

            $this->validateStruct( $struct );

            TreeDummy::findSingle( [ 'name' => 'a' ] )->under( TreeDummy::findSingle( [ 'name' => 'h' ] ) )->save();

            $struct = [
                "e" => [
                    "f" => [
                        "g" => [],
                        "h" => [
                            "a" => [
                                "c" => [],
                            ],
                        ]
                    ],
                    "b" => [
                        "d" => []
                    ],
                ],
            ];

            $this->validateStruct( $struct );
        }

        public function testPositionedMove() {

            $struct = [
                "a" => [
                    "a1" => [],
                    "a2" => [],
                    "a3" => [],
                ],
                "b" => [],
                "c" => [],
                "d" => [],
            ];

            $this->createStructure( $struct );

            $move = TreeDummy::findSingle( [ 'name' => 'c' ] );
            $move->move()->after( TreeDummy::findSingle( [ 'name' => 'a2' ] ) )->save();

            $this->validateStruct( [
                "a" => [
                    "a1" => [],
                    "a2" => [],
                    "c"  => [],
                    "a3" => [],
                ],
                "b" => [],
                "d" => [],
            ] );

            $move = TreeDummy::findSingle( [ 'name' => 'c' ] );
            $move->move()->before( TreeDummy::findSingle( [ 'name' => 'a2' ] ) )->save();

            $this->validateStruct( [
                "a" => [
                    "a1" => [],
                    "c"  => [],
                    "a2" => [],
                    "a3" => [],
                ],
                "b" => [],
                "d" => [],
            ] );

            $move = TreeDummy::findSingle( [ 'name' => 'b' ] );

            // at start
            $move->move()->under( TreeDummy::findSingle( [ 'name' => 'a' ] ), false )->save();

            $this->validateStruct( [
                "a" => [
                    "b"  => [],
                    "a1" => [],
                    "c"  => [],
                    "a2" => [],
                    "a3" => [],
                ],
                "d" => [],
            ] );


            $move = TreeDummy::findSingle( [ 'name' => 'd' ] );

            // at end
            $move->move()->under( TreeDummy::findSingle( [ 'name' => 'a' ] ), true )->save();

            $this->validateStruct( [
                "a" => [
                    "b"  => [],
                    "a1" => [],
                    "c"  => [],
                    "a2" => [],
                    "a3" => [],
                    "d"  => [],
                ],
            ] );
        }

        public function testInsert() {

            $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
            ];

            $this->createStructure( $struct );

            $instance = new TreeDummy();
            $instance->setName( "i" );
            $instance->save();

            $this->validateStruct( $struct = [
                "a" => [],
                "b" => [],
                "c" => [],
                "i" => [],
            ] );

            $instance = new TreeDummy();
            $instance->setName( "i2" );
            $instance->after( TreeDummy::findSingle( [ 'name' => 'a' ] ) );
            $instance->save();


            $this->validateStruct( $struct = [
                "a" => [],
                "i2" => [],
                "b" => [],
                "c" => [],
                "i" => [],
            ] );
        }

    }
