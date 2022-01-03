<?php

    namespace IdentifierTest;

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\Driver\Postgres;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

        #[ \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase ]
        public mixed $TEST;

        #[ \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase ]
        public mixed $snakeCase;

        #[ \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase ]
        public mixed $StrAngECAse;

        public function getSnakeCase(): mixed {
            return $this->snakeCase;
        }

        public function setSnakeCase( mixed $snakeCase ): static {
            $this->snakeCase = $snakeCase;
            return $this;
        }

        public function getTEST(): mixed {
            return $this->TEST;
        }

        public function setTEST( mixed $TEST ): static {
            $this->TEST = $TEST;
            return $this;
        }

        public function getStrAngECAse(): mixed {
            return $this->StrAngECAse;
        }

        public function setStrAngECAse( mixed $StrAngECAse ): static {
            $this->StrAngECAse = $StrAngECAse;
            return $this;
        }

    }

    class A
    extends DataModel {

        public ?int $id = null;

        #[ \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase ]
        public ?int $bId = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getBId(): ?int {
            return $this->bId;
        }

        public function setId( ?int $id ): static {
            $this->id = $id;
            return $this;
        }

        public function setBId( ?int $bId ): static {
            $this->bId = $bId;
            return $this;
        }

    }

    class B
    extends DataModel {

        public ?int $id = null;

        #[ \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase ]
        public ?int $aId = null;

        public function getId(): ?int {
            return $this->id;
        }

        public function getAId(): ?int {
            return $this->aId;
        }

        public function setId( ?int $id ): static {
            $this->id = $id;
            return $this;
        }

        public function setAId( ?int $aId ): static {
            $this->aId = $aId;
            return $this;
        }

    }

    class IdentifierTest
    extends TestCase {

        public function testInit(): void {
            $ident = new Identifier( 'test' );
            static::assertSame( 'test', $ident->stringify() );
        }

        public function testTable(): void {
            $ident = new Identifier( 'table', 'column' );
            static::assertSame( 'table.column', $ident->stringify() );
        }

        public function testSchema(): void {
            $ident = new Identifier( 'schema', 'table', 'column' );
            static::assertSame( 'schema.table.column', $ident->stringify() );
        }

        public function testQuotedIdent(): void {

            $driver = new Postgres( 'test', 'test', 'test', 'test', 'test' );

            $ident = new Identifier( 'column' );

            static::assertSame( '"column"', $ident->stringify( $driver ) );
        }

        public function testTranslatedIdentifier(): void {

            $ident = new Identifier( 'TEST' );
            $ident->addDataModelContext( new Example() );

            static::assertSame( "test", $ident->stringify() );

            $ident = new Identifier( 'test' );
            $ident->addDataModelContext( new Example() );
            static::assertSame( "test", $ident->stringify() );

            $ident = new Identifier( 'snakecase' );
            $ident->addDataModelContext( new Example() );
            static::assertSame( "snakecase", $ident->stringify() );

            $ident = new Identifier( 'snakeCase' );
            $ident->addDataModelContext( new Example() );
            static::assertSame( "snakecase", $ident->stringify() );

            $ident = new Identifier( 'StrAngECAse' );
            $ident->addDataModelContext( new Example() );
            static::assertSame( "strangecase", $ident->stringify() );
        }

        public function testTranslateMultipleContext(): void {


            $ident = new Identifier( 'bId' );
            $ident->addDataModelContext( new A );
            $ident->addDataModelContext( new B );

            static::assertSame( 'b_id', $ident->stringify() );
        }

    }
