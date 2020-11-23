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
            static::$connection->query( 'create table "TreeDummy" ( id serial primary key, name text, "left" int, "right" int, parent_id int, depth int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists "TreeDummy";' );
        }

        public function test() {
            new TreeDummy;
        }

        public function testSave() {

            // first
            $dummy = new TreeDummy;
            $dummy->setName( 'nice' );
            $dummy->save();

            $dummy = TreeDummy::get( 1 );

            $this->assertSame( 1, $dummy->getId(), 'id' );
            $this->assertSame( 1, $dummy->getLeft(), 'left' );
            $this->assertSame( 2, $dummy->getRight(), 'right' );
            $this->assertSame( 0, $dummy->getDepth(), 'depth' );
            $this->assertSame( 'nice', $dummy->getName(), 'name' );
            $this->assertSame( null, $dummy->getParentId(), 'parent' );

            // second
            $dummy = new TreeDummy;
            $dummy->setName( 'nice2' );
            $dummy->save();

            $dummy = TreeDummy::get( 2 );

            $this->assertSame( 2, $dummy->getId(), 'id' );
            $this->assertSame( 3, $dummy->getLeft(), 'left' );
            $this->assertSame( 4, $dummy->getRight(), 'right' );
            $this->assertSame( 0, $dummy->getDepth(), 'depth' );
            $this->assertSame( 'nice2', $dummy->getName(), 'name' );
            $this->assertSame( null, $dummy->getParentId(), 'parent' );

            // second
            $dummy = new TreeDummy;
            $dummy->setName( 'nice3' );
            $dummy->save();

            $dummy->reload();

            $this->assertSame( 3, $dummy->getId(), 'id' );
            $this->assertSame( 5, $dummy->getLeft(), 'left' );
            $this->assertSame( 6, $dummy->getRight(), 'right' );
            $this->assertSame( 0, $dummy->getDepth(), 'depth' );
            $this->assertSame( 'nice3', $dummy->getName(), 'name' );
            $this->assertSame( null, $dummy->getParentId(), 'parent' );
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
            $this->assertSame( 1, $parent->getLeft(), 'left' );
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

            $parent = new TreeDummy;
            $parent->setName( 'parent' );
            $parent->save();

            $child = new TreeDummy;
            $child->under( $parent );
            $child->setName( 'child' );
            $child->save();

            $child2 = new TreeDummy;
            $child2->under( $parent );
            $child2->setName( '2nd child' );
            $child2->save();

            $child3 = new TreeDummy;
            $child3->under( $child2 );
            $child3->setName( '3nd child' );
            $child3->save();

            $parent->reload();
            $child->reload();
            $child2->reload();

            // update
            $child3->setName( 'update' )->save();
            $child3 = TreeDummy::get( 4 );

            $this->assertSame( 'update', $child3->getName() );

            $this->assertSame( 5, $child3->getLeft(), 'left' );
            $this->assertSame( 6, $child3->getRight(), 'right' );
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

        public function validateStruct( $struct, &$c = 0 ) {


            foreach ( $struct as $e => $s ) {

                $instance = TreeDummy::findSingle( [ 'name' => $e ] );

                $this->assertSame( ++$c, $instance->getLeft(), $e . ' left' );

                $this->validateStruct( $s, $c );

                $this->assertSame( ++$c, $instance->getRight(), $e . ' right' );
            }
        }

        public function getStruct( $struct, &$res = [] ) {
            foreach ( $struct as $e => $s ) {
                $res[] = TreeDummy::findSingle( [ 'name' => $e ] );
                $this->getStruct( $s, $res );
            }

            return $res;
        }

        public function testMove() {

            $struct = [
                "a" => [
                    "c" => [],
                ],
                "b" => [],
            ];

            $this->createStructure( $struct );
            [$a, $c, $b] = $this->getStruct( $struct );

            $c->move()->under( $b );
            $c->save();

            $struct = [
                "a" => [],
                "b" => [
                    "c" => [],
                ],
            ];

            $this->validateStruct( $struct );


            $b->move()->under( $a )->save();

            $struct = [
                "a" => [
                    "b" => [
                        "c" => [],
                    ]
                ],
            ];

            $this->validateStruct( $struct );

            $c->move()->under( $a )->save();

            $struct = [
                "a" => [
                    "c" => [],
                    "b" => [],
                ],
            ];

            $this->validateStruct( $struct );
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
                    "b" => [
                        "c" => [],
                        "d" => []
                    ],
                    "f" => [
                        "g" => [],
                        "h" => []
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
                    "b" => [
                        "d" => []
                    ],
                    "f" => [
                        "g" => [],
                        "h" => []
                    ],
                ],
            ];

            $this->validateStruct( $struct );

            TreeDummy::findSingle( [ 'name' => 'a' ] )->under( TreeDummy::findSingle( [ 'name' => 'h' ] ) )->save();

            $struct = [
                "e" => [
                    "b" => [
                        "d" => []
                    ],
                    "f" => [
                        "g" => [],
                        "h" => [
                            "a" => [
                                "c" => [],
                            ],
                        ]
                    ],
                ],
            ];

            $this->validateStruct( $struct );
        }

    }
