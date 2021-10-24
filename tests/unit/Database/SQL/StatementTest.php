
<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\From;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Where;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Insert;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\Database\SQL\Statement;

    class StatementTest
    extends TestCase {

        public function testMinimalSelect() {
            $statement = new Statement( (new Select() )->add( new Value( true ) ) );
            $this->assertSame( 'SELECT true', $statement->stringify() );
        }

        public function testNestedSelect() {
            $statement = new Statement(
                (new Select() )
                    ->add(
                        (new Select() )
                        ->add( new Value( true ) )
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
            $statement->add( new Where( new Value( true ) ) );
            $this->assertSame( 'SELECT column_a FROM table WHERE true', $statement->stringify() );
        }

        public function testInsert() {
            $statement = new Statement(
                (new Insert( new Identifier( 'test' ) ) )
                    ->add(
                        new Identifier( 'column_a' )
                    )
            );
            $statement->add( (new Select() )->add( new Value( true ) ) );
            $this->assertSame( 'INSERT INTO test ( column_a ) SELECT true', $statement->stringify() );
        }

        public function testDataBindings() {

            $statement = new Statement(
                (new Select() )
                    ->add(
                        (new Select )
                        ->add( new Value( 15 ) )
                        ->add( new Value( 1 ) )
                    )
            );

            $this->assertSame( [ 15, 1 ], array_values( $statement->fetchBindings() ) );
        }

        public function testDataBindingsClause() {

            $statement = new Statement(
                (new Select() )
                    ->add(
                        (new Select )
                        ->add( new Value( 15 ) )
                        ->add( new Value( 1 ) )
                    )
            );
            $statement->add( new Where( new Value( 666 ) ) );

            $this->assertTrue( in_array( 666, array_values( $statement->fetchBindings() ) ) );
            $this->assertTrue( in_array( 1, array_values( $statement->fetchBindings() ) ) );
        }

    }
