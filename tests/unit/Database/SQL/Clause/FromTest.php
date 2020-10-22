<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Primitive;

    class FromTest
    extends TestCase {

        public function testSimple() {
            $from = new From( new Identifier( 'table' ) );
            $this->assertSame( 'FROM table', $from->stringify() );
        }

        public function testSimpleAlias() {
            $from = new From(
                (new Identifier( 'table' ) )
                    ->addAlias( new Identifier( 'tb' ) )
            );
            $this->assertSame( 'FROM table AS tb', $from->stringify() );
        }

        public function testFromExpression() {
            $from = new From(
                (new Select( ) )->add( new Primitive( true ) )
            );
            $this->assertSame( 'FROM ( SELECT true )', $from->stringify() );
        }

    }
