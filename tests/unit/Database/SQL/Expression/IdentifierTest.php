<?php

    namespace IdentifierTest;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\Database\SQL\Expression\Identifier;

    class Example extends \Wrapped\_\DataModel\DataModel {

        #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $TEST;

        #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]
        public $snakeCase;

        #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]
        public $StrAngECAse;

        public function getSnakeCase() {
            return $this->snakeCase;
        }

        public function setSnakeCase( $snakeCase ) {
            $this->snakeCase = $snakeCase;
            return $this;
        }

        public function getTEST() {
            return $this->TEST;
        }

        public function setTEST( $TEST ) {
            $this->TEST = $TEST;
            return $this;
        }

        public function getStrAngECAse() {
            return $this->StrAngECAse;
        }

        public function setStrAngECAse( $StrAngECAse ) {
            $this->StrAngECAse = $StrAngECAse;
            return $this;
        }

    
    }

    class IdentifierTest
    extends TestCase {

        public function testInit() {
            $ident = new Identifier( 'test' );
            $this->assertSame( 'test', $ident->stringify() );
        }

        public function testTable() {
            $ident = new Identifier( 'table', 'column' );
            $this->assertSame( 'table.column', $ident->stringify() );
        }

        public function testSchema() {
            $ident = new Identifier( 'schema', 'table', 'column' );
            $this->assertSame( 'schema.table.column', $ident->stringify() );
        }

        public function testQuotedIdent() {

            $driver = new Postgres( 'test', 'test', 'test', 'test', 'test' );

            $ident = new Identifier( 'column' );

            $this->assertSame( '"column"', $ident->stringify( $driver ) );
        }

        public function testTranslatedIdentifier() {

            $ident = new Identifier( 'TEST' );
            $ident->addDataModelContext( new Example() );

            $this->assertSame( "test", $ident->stringify() );

            $ident = new Identifier( 'test' );
            $ident->addDataModelContext( new Example() );
            $this->assertSame( "test", $ident->stringify() );

            $ident = new Identifier( 'snakecase' );
            $ident->addDataModelContext( new Example() );
            $this->assertSame( "snakecase", $ident->stringify() );

            $ident = new Identifier( 'snakeCase' );
            $ident->addDataModelContext( new Example() );
            $this->assertSame( "snakecase", $ident->stringify() );

            $ident = new Identifier( 'StrAngECAse' );
            $ident->addDataModelContext( new Example() );
            $this->assertSame( "strangecase", $ident->stringify() );
        }

    }
