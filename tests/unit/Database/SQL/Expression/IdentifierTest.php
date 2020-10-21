<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Driver\Postgres;
    use \Wrapped\_\Database\SQL\Expression\Identifier;

    class IdentifierTest
    extends TestCase {

        public function testInit() {
            $ident = new Identifier( 'test' );
            $this->assertSame( 'test', $ident->stringify() );
        }

        public function testTable() {
            $ident = new Identifier( 'column', 'table' );
            $this->assertSame( 'table.column', $ident->stringify() );
        }

        public function testSchema() {
            $ident = new Identifier( 'column', 'table', 'schema' );
            $this->assertSame( 'schema.table.column', $ident->stringify() );
        }

        public function testMissingTable() {

            $this->expectExceptionObject( new Exception( 'table ident is missing' ) );
            $ident = new Identifier( 'column', null, 'schema' );
        }

        public function testQuotedIdent() {

            $driver = new Postgres( 'test', 'test', 'test', 'test', 'test' );

            $ident = new Identifier( 'column' );
            $ident->setConnection( $driver );

            $this->assertSame( '"column"', $ident->stringify() );
        }

    }
