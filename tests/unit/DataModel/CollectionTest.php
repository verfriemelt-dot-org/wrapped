<?php

    use \PHPUnit\Framework\TestCase;
    use \Wrapped\_\DataModel\Collection;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionDummy
    extends DataModel {

        static int $counter = 0;

        public int $id;

        public function __construct() {
            $this->setId( ++static::$counter );
        }

        function getId(): ?int {
            return $this->id;
        }

        public function setId( int $offset ) {
            $this->id = $offset;
            return $this;
        }

    }

    class CollectionTest
    extends TestCase {

        public function setUp(): void {
            parent::setUp();

            CollectionDummy::$counter = 0;
        }

        public function testCollectionInit() {
            $collection = new Collection( );
            $this->assertSame( 0, $collection->count() );
            $this->assertTrue( $collection->isEmpty() );
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

            foreach ( $collection as $instance ) {
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

            $result = $collection->map( fn( CollectionDummy $d ) => $d->getId() );

            // iterate all
            $this->assertSame( 10, count( $result ) );
        }

        public function testArrayAccess() {

            $callback = function ( $offset ): DataModel {
                return (new CollectionDummy() )->setId( $offset + 1 );
            };

            $collection = new Collection( );

            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            // 5th element in array should have id of 5
            $this->assertSame( 5, $collection[4]->getId() );

            $this->expectExceptionObject( new \Exception( 'illegal offset' ) );
            $collection[11];
        }

        public function testStartEndGetter() {

            $collection = new Collection( );

            $this->assertNull( $collection->last() );
            $this->assertNull( $collection->first() );


            $callback = function ( $offset ): DataModel {
                return (new CollectionDummy() )->setId( $offset + 1 );
            };


            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            // last element
            $this->assertSame( 10, $collection->last()->getId() );
            $this->assertSame( 1, $collection->first()->getId() );
        }

        public function testSeek() {
            $callback = function ( $offset ): DataModel {
                return (new CollectionDummy() )->setId( $offset + 1 );
            };

            $collection = new Collection( );
            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            $collection->seek( 4 );
            // 5th element in array should have id of 5
            $this->assertSame( 5, $collection->current()->getId() );

            $this->expectExceptionObject( new \OutOfBoundsException() );
            $collection->seek( 11 );
        }

        public function testIllegalOffget() {

            $callback = function ( $offset ): DataModel {
                return (new CollectionDummy() )->setId( $offset + 1 );
            };

            $collection = new Collection( );
            $collection->setLength( 10 );
            $collection->setLoadingCallback( $callback );

            $this->expectExceptionObject( new \Exception( 'illegal offset' ) );
            // trigger exception
            $collection[-1];
        }

        public function testIllegalOffset() {

            $collection = new Collection;

            $this->expectExceptionObject( new \Exception( 'write only' ) );
            $collection[1] = new CollectionDummy;
        }

        public function testIllegalOffunset() {

            $collection = new Collection;

            $this->expectExceptionObject( new \Exception( 'write only' ) );
            unset( $collection[1] );
        }

    }
