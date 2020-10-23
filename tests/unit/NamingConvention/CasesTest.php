<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \Wrapped\_\DataModel\Attribute\Naming\LowerCase;
    use \Wrapped\_\DataModel\Attribute\Naming\SpaceCase;

    class CasesTest
    extends TestCase {

        public function testSpaceCase() {

            $case = new SpaceCase( 'test case experiment' );
            $this->assertSame( [ 'test', 'case', 'experiment' ], $case->fetchStringParts() );

            $camelcase = $case->convertTo( CamelCase::class );

            $this->assertSame( 'testCaseExperiment', $camelcase->getString() );
        }

        public function testLowerCase() {

            $case = LowerCase::fromStringParts( ... [ 'space', 'seperated', 'text' ] );
            $this->assertSame( 'spaceseperatedtext', $case->getString() );

            $case = new SpaceCase( 'space seperated text' );
            $lc   = $case->convertTo( LowerCase::class );

            $this->assertSame( 'spaceseperatedtext', $lc->getString() );
        }

        public function testCamelCase() {

            $case = new CamelCase( 'thisIsSparta' );

            $this->assertSame( [ 'this', 'is', 'sparta' ], $case->fetchStringParts() );

            $this->assertSame( 'this is sparta', $case->convertTo( SpaceCase::class )->getString() );
        }

    }
