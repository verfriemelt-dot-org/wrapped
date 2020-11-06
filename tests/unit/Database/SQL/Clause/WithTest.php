<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\SQL\Clause\CTE;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Statement;

    class WithTest
    extends TestCase {

        public function testMinimal() {

            $cte = new CTE;
            $cte->with( new Identifier( 'test' ), new Statement( new Select( new Primitive( true ) ) ) );
            $this->assertSame( 'WITH test AS ( SELECT true )', $cte->stringify() );
        }

        public function testMultiple() {

            $cte = new CTE;
            $cte->with( new Identifier( 'test' ), new Statement( new Select( new Primitive( true ) ) ) );
            $cte->with( new Identifier( 'test2' ), new Statement( new Select( new Primitive( null ) ) ) );
            $this->assertSame( 'WITH test AS ( SELECT true ), test2 AS ( SELECT null )', $cte->stringify() );
        }

    }
