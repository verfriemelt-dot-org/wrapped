<?php

    namespace testcase\datamodeltest;

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

    }

    #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase]
    class Example2
    extends DataModel {

    }

    #[\verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase]

    class LongerExample
    extends DataModel {

    }

    class FunctionDataModelAttributeTest
    extends TestCase {

        public function testDatabaseNames() {
            $this->assertSame( 'Example', Example::fetchTablename() );
            $this->assertSame( null, Example::fetchSchemaname() );
        }

        public function testCasingConvention() {
            $this->assertSame( 'example2', Example2::fetchTablename() );
            $this->assertSame( 'longer_example', LongerExample::fetchTablename(), 'snake case' );
        }

    }
