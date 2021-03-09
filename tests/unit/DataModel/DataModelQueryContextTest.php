<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

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

    class Example2
    extends DataModel {

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $NAME;

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $exampleId;

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

        public function getExampleId() {
            return $this->exampleId;
        }

        public function setExampleId( $exampleId ) {
            $this->exampleId = $exampleId;
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

            $ident = new Identifier( 'public', 'Example', 'NAME' );
            $ident->addDataModelContext( new Example );
            $this->assertSame( 'public.Example.name', $ident->stringify() );
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


            // adding context after creating children
            $select = new Select();
            $select->add( new Identifier( 'Example2', 'NAME' ) );

            $select->addDataModelContext( new Example2 );
            $this->assertSame( 'SELECT Example2.name', $select->stringify() );
        }

        public function testMultipleContextAmbiguous() {

            $this->expectExceptionObject( new Exception( 'ambiguous' ) );

            $select = new Select();
            $select->addDataModelContext( new Example );
            $select->addDataModelContext( new Example2 );

            $select->add( new Identifier( 'NAME' ) );
            $this->assertSame( 'SELECT name', $select->stringify() );
        }

        public function testMultipleContext() {

            $select = new Select();
            $select->addDataModelContext( new Example );
            $select->addDataModelContext( new Example2 );

            $select->add( new Identifier( 'exampleId' ) );
            $this->assertSame( 'SELECT exampleid', $select->stringify() );
        }

    }
