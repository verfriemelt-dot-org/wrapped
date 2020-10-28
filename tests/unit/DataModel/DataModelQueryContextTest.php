<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

        #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $NAME;

        public $id;

        public function getNAME() {
            return $this->NAME;
        }

        public function getId() {
            return $this->id;
        }

        public function setNAME( $NAME ) {
            $this->NAME = $NAME;
            return $this;
        }

        public function setId( $id ) {
            $this->id = $id;
            return $this;
        }

    }

    class DataModelQueryContextTest
    extends TestCase {

        public function testContext() {

            $ident = new Identifier( 'NAME' );
            $this->assertSame( 'NAME', $ident->stringify() );

            $ident->addDataModelContext( new Example );
            $this->assertSame( 'name', $ident->stringify() );
        }

        public function testContextFQN() {

            $ident = new Identifier( 'schema', 'table', 'NAME' );
            $this->assertSame( 'schema.table.NAME', $ident->stringify() );

            $ident->addDataModelContext( new Example );
            $this->assertSame( 'schema.table.name', $ident->stringify() );
        }

        public function testPassingDownContext() {

            $select = new Select();
            $select->addDataModelContext( new Example );

            $select->add( new Identifier( 'NAME' ) );
            $this->assertSame( 'SELECT name', $select->stringify() );


            // adding context after creating children
            $select = new Select();
            $select->add( new Identifier( 'NAME' ) );

            $select->addDataModelContext( new Example );

            $this->assertSame( 'SELECT name', $select->stringify() );
        }

    }
