<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\CTE;
    use \verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\Database\SQL\Statement;

    class WithTest
    extends TestCase {

        public function testMinimal() {

            $cte = new CTE;
            $cte->with( new Identifier( 'test' ), new Statement( new Select( new Value( true ) ) ) );
            $this->assertSame( 'WITH test AS ( SELECT true )', $cte->stringify() );
        }

        public function testMultiple() {

            $cte = new CTE;
            $cte->with( new Identifier( 'test' ), new Statement( new Select( new Value( true ) ) ) );
            $cte->with( new Identifier( 'test2' ), new Statement( new Select( new Value( null ) ) ) );
            $this->assertSame( 'WITH test AS ( SELECT true ), test2 AS ( SELECT NULL )', $cte->stringify() );
        }

    }
