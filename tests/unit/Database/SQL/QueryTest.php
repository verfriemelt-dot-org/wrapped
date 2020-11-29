
<?php

    use \PHPUnit\Framework\TestCase;

    class QueryTest
    extends TestCase {

        public function testWhereWithValueFromArray() {

            $query = new \Wrapped\_\Database\Facade\QueryBuilder();

            $query->select( 'column' );
            $query->from( "table" );
            $query->where( [
                "column" => 1
            ] );

            $this->assertStringContainsString( 'SELECT column FROM table WHERE column = ', $query->fetchStatement()->stringify() );
        }

        public function testWhereWithNullFromArray() {

            $query = new \Wrapped\_\Database\Facade\QueryBuilder();

            $query->select( 'column' );
            $query->from( "table" );
            $query->where( [
                "column" => null
            ] );

            $this->assertStringContainsString( 'SELECT column FROM table WHERE column IS NULL', $query->fetchStatement()->stringify() );
        }

    }
