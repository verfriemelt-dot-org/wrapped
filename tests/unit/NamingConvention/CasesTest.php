<?php

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\PascalCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SpaceCase;

    class CasesTest
    extends TestCase {

        public function testSpaceCase(): void {

            $case = new SpaceCase( 'test case experiment' );
            static::assertSame( [ 'test', 'case', 'experiment' ], $case->fetchStringParts() );

            $camelcase = $case->convertTo( CamelCase::class );

            static::assertSame( 'testCaseExperiment', $camelcase->getString() );
        }

        public function testLowerCase(): void {

            $case = LowerCase::fromStringParts( ... [ 'space', 'seperated', 'text' ] );
            static::assertSame( 'spaceseperatedtext', $case->getString() );

            $case = new SpaceCase( 'space seperated text' );
            $lc   = $case->convertTo( LowerCase::class );

            static::assertSame( 'spaceseperatedtext', $lc->getString() );
        }

        public function testCamelCase(): void {

            $case = new CamelCase( 'thisIsSparta' );

            static::assertSame( [ 'this', 'is', 'sparta' ], $case->fetchStringParts() );
            static::assertSame( 'thisIsSparta', CamelCase::fromStringParts( ... [ 'this', 'is', 'sparta' ] )->getString() );
        }

        public function testPascalCase(): void {

            $case = new PascalCase( 'thisIsSparta' );

            static::assertSame( [ 'this', 'is', 'sparta' ], $case->fetchStringParts() );
            static::assertSame( 'ThisIsSparta', PascalCase::fromStringParts( ... [ 'this', 'is', 'sparta' ] )->getString() );
        }

        public function testConversion(): void {

            $case = (new CamelCase( 'complexFieldNameSnakeCase' ) )->convertTo( new SnakeCase );
            static::assertSame( 'complex_field_name_snake_case', $case->getString() );
        }

    }
