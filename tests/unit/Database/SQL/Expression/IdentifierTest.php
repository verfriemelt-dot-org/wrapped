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

    }
    