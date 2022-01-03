<?php

    namespace testcase\datamodeltest;

    use \PHPUnit\Framework\TestCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    class Example
    extends DataModel {

    }

    #[ LowerCase ]
    class Example2
    extends DataModel {

    }

    #[ SnakeCase ]
    class LongerExample
    extends DataModel {

    }

    class DataModelTest
    extends TestCase {

        public function testDatabaseNames(): void {
            static::assertSame( 'Example', Example::fetchTablename() );
            static::assertSame( null, Example::fetchSchemaname() );
        }

        public function testCasingConvention(): void {
            static::assertSame( 'example2', Example2::fetchTablename() );
            static::assertSame( 'longer_example', LongerExample::fetchTablename(), 'snake case' );
        }

    }
