<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\Collection;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionDummy
    extends DataModel {

        static int $counter = 0;

        function getId(): ?int {
            return ++static::$counter;
        }

    }

    class CollectionTest
    extends TestCase {

        public function testCollectionInit() {
            $collection = new Collection( );
            $this->assertSame( 0, $collection->count() );
        }

        public function testCollectionLength() {

            $collection = new Collection( ... [
                new CollectionDummy,
                new CollectionDummy
                ] );

            $this->assertSame( 2, $collection->count() );
        }

        public function testCallback() {

            $callback = function ( $offset ): DataModel {

                if ( $offset < 10 ) {
                    return new CollectionDummy();
                }

                throw new Exception( 'empty' );
            };

            $collection = new Collection( );
            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            $this->assertSame( 10, $collection->count() );

            $counter = 0;

            foreach( $collection as $instance ) {
                $this->assertTrue( $instance instanceof CollectionDummy );
                $counter++;
            }

            $this->assertSame( 10, $counter );
        }

        public function testMap() {

            $callback = function ( $offset ): DataModel {

                if ( $offset < 10 ) {
                    return new CollectionDummy();
                }

                throw new Exception( 'empty' );
            };

            $collection = new Collection( );
            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            $this->assertSame( 10, $collection->count() );

            $result = $collection->map( fn ( CollectionDummy $d ) => $d->getId() );

            $this->assertSame( 10, count( $result ) );
        }

    }
