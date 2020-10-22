
<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Statement;

    class StatementTest
    extends TestCase {

        public function testInit() {
            new Statement( new Select() );
        }

        public function testMinimalSelect() {
            $statement = new Statement( (new Select() )->add( new Primitive( true ) ) );
            $this->assertSame( 'SELECT true', $statement->stringify() );
        }

        public function testNestedSelect() {
            $statement = new Statement(
                (new Select() )
                    ->add(
                        (new Select() )
                        ->add( new Primitive( true ) )
                    )
            );
            $this->assertSame( 'SELECT ( SELECT true )', $statement->stringify() );
        }

        public function testSimpleQuery() {
            $statement = new Statement(
                (new Select() )
                    ->add(
                        new Identifier( 'column_a' )
                    )
            );

            $statement->add( new From( new Identifier( 'table' ) ) );
            $statement->add( new Where( new Primitive( true ) ) );
            $this->assertSame( 'SELECT column_a FROM table WHERE true', $statement->stringify() );
        }

    }
