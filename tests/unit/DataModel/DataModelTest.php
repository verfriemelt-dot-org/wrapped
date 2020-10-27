<?php

    namespace testcase\datamodeltest;

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

    }

    #[\Wrapped\_\DataModel\Attribute\Naming\LowerCase]
    class Example2
    extends DataModel {

    }

    #[\Wrapped\_\DataModel\Attribute\Naming\SnakeCase]

    class LongerExample
    extends DataModel {

    }

    class FunctionDataModelAttributeTest
    extends TestCase {

        public function testDatabaseNames() {
            $this->assertSame( 'Example', Example::getTableName() );
            $this->assertSame( null, Example::getSchemaName() );
        }

        public function testCasingConvention() {
            $this->assertSame( 'example2', Example2::getTableName() );
            $this->assertSame( 'longer_example', LongerExample::getTableName(), 'snake case' );
        }

    }
