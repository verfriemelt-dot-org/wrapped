
<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\Database\Facade\Query;

    class QueryTest
    extends TestCase {

        public function testWhereWithValueFromArray() {

            $query = new Query();

            $query->select( 'column' );
            $query->from( "table" );
            $query->where( [
                "column" => 1
            ] );

            $this->assertStringContainsString( 'SELECT column FROM table WHERE column = ', $query->fetchStatement()->stringify() );
        }

        public function testWhereWithNullFromArray() {

            $query = new Query();

            $query->select( 'column' );
            $query->from( "table" );
            $query->where( [
                "column" => null
            ] );

            $this->assertStringContainsString( 'SELECT column FROM table WHERE column IS null', $query->fetchStatement()->stringify() );
        }

    }
