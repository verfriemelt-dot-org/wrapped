<?php

    namespace integration\DataModel\SimpleTreeDataModel;

    use \DatabaseTestCase;
    use \verfriemelt\wrapped\_\Database\Driver\SQLite;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
    use \verfriemelt\wrapped\_\DataModel\Tree\SimpleTreeDataModel;

    #[ LowerCase ]
    class Tree
    extends SimpleTreeDataModel {

        public ?int $id = null;

        public ?int $parentId = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getParentId(): ?int {
            return $this->parentId;
        }

        public function setId( ?int $id ): void {
            $this->id = $id;
        }

        public function setParentId( ?int $parentId ): void {
            $this->parentId = $parentId;
        }

    }

    class SimpleTreeDataModelTest
    extends DatabaseTestCase {

        public function setUp(): void {

            if ( static::$connection instanceof SQLite ) {
                $this->markTestSkipped( 'sqlite not supported' );
                return;
            }

            static::$connection->query( 'create table tree ( id serial primary key, parent_id int );' );
        }

        public function tearDown(): void {
            static::$connection->query( 'drop table if exists tree;' );
        }

        public function saveInstance( $class, $name = 'test' ) {

            $obj = new $class;
            $obj->save();

            return $obj;
        }

        public function testNestedSave() {

            $obj1 = new Tree();
            $obj1->save();

            $obj2 = new Tree();
            $obj2->under( $obj1 );
            $obj2->save();

            $this->assertSame( $obj1->getId(), $obj2->getParentId() );
            $this->assertSame( $obj1->getId(), $obj2->fetchParent()->getId() );
        }

        public function testChildren() {

            $obj1 = new Tree();
            $obj1->save();

            $obj2 = new Tree();
            $obj2->under( $obj1 );
            $obj2->save();

            $obj3 = new Tree();
            $obj3->under( $obj1 );
            $obj3->save();

            $obj4 = new Tree();
            $obj4->under( $obj3 );
            $obj4->save();

            $this->assertSame( 2, $obj1->fetchDirectChildren()->count() );
            $this->assertSame( 3, $obj1->fetchChildren()->count() );

            $this->assertSame( 3, $obj1->fetchChildCount() );
            $this->assertSame( 0, $obj2->fetchChildCount() );
            $this->assertSame( 1, $obj3->fetchChildCount() );
        }

        public function testFetchParent() {

            $obj1 = new Tree();
            $obj1->save();

            $obj2 = new Tree();
            $obj2->under( $obj1 );
            $obj2->save();

            $obj3 = new Tree();
            $obj3->under( $obj1 );
            $obj3->save();

            $obj4 = new Tree();
            $obj4->under( $obj3 );
            $obj4->save();

            $this->assertSame( $obj3->getId(), $obj4->fetchParent()->getId() );
            $this->assertSame( $obj1->getId(), $obj4->fetchParent()->fetchParent()->getId() );
            $this->assertNull( $obj4->fetchParent()->fetchParent()->fetchParent() );
        }

    }
