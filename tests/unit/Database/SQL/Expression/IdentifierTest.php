<?php

    namespace IdentifierTest;

use \PHPUnit\Framework\TestCase;
use \verfriemelt\wrapped\_\Database\Driver\Postgres;
use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use \verfriemelt\wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $TEST;

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

        public $snakeCase;

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]

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


   class A
    extends DataModel {

        public ?int $id = null;

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?int $bId = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getBId(): ?int {
            return $this->bId;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setBId( ?int $bId ) {
            $this->bId = $bId;
            return $this;
        }

    }

    class B
    extends DataModel {

        public ?int $id = null;

        #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase]

        public ?int $aId = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getAId(): ?int {
            return $this->aId;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setAId( ?int $aId ) {
            $this->aId = $aId;
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

        public function testTranslateMultipleContext() {


            $ident = new Identifier('bId');
            $ident->addDataModelContext( new A );
            $ident->addDataModelContext( new B );

            $this->assertSame( 'b_id' , $ident->stringify() );

        }

    }
