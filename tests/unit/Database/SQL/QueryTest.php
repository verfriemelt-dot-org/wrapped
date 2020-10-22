
<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Statement;

    class QueryTest
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

    }
