<?php

    use \Wrapped\_\Queue\Backend\MemoryBackend;
    use \Wrapped\_\Queue\Queue;
    use \Wrapped\_\Queue\QueueItem;

    class QueueTest
    extends \PHPUnit\Framework\TestCase {

        public function testCreateInstance() {
            $queue = new Queue( new MemoryBackend );
            $this->assertTrue( $queue instanceof Queue );
        }

        public function testAddingQueueItem() {

            $queue = new Queue( new MemoryBackend );
            $queue->add( new QueueItem( "testing" ) );
            $queue->add( new QueueItem( "testing" ) );

            $result = $queue->fetchByKey( "testing" );

            foreach ( $result as $queueItem ) {
                $this->assertTrue( $queueItem instanceof QueueItem );
            }
        }

        public function testEmptyQueue() {

            $queue = new Queue( new MemoryBackend );

            $this->assertEquals( [], $queue->fetchByKey( "testing" ) );
        }

        public function testQueueSepartion() {

            $queue = new Queue( new MemoryBackend );
            $queue->add( new QueueItem( "testing" ) );
            $queue->add( new QueueItem( "testing" ) );
            $queue->add( new QueueItem( "b" ) );
            $queue->add( new QueueItem( "testing", "email" ) );
            $queue->add( new QueueItem( "b", "email" ) );

            $this->assertEquals( 2, count( $queue->fetchByKey( "testing" ) ) );
            $this->assertEquals( 1, count( $queue->fetchByKey( "b" ) ) );
            $this->assertEquals( 1, count( $queue->fetchByKey( "testing", "email" ) ) );
            $this->assertEquals( 1, count( $queue->fetchByKey( "b", "email" ) ) );
        }

        public function testDeleteItemFromQueue() {

            $backend = new MemoryBackend;
            $queue   = new Queue( $backend );
            $queue->add( new QueueItem( "b" ) );

            $this->assertEquals( 1, count( $queue->fetchByKey( "b" ) ) );

            foreach ( $queue->fetchByKey( "b" ) as $item ) {
                $item->delete();
            }

            $this->assertEquals( [], $queue->fetchByKey( "b" ) );
        }

        public function testAlterQueueItem() {


            $backend = new MemoryBackend;
            $queue   = new Queue( $backend );

            $queue->add( new QueueItem( "b" ) );

            foreach ( $queue->fetchByKey( "b" ) as $item ) {
                $item->setData( 5 );
            }

            $this->assertEquals( 5, $queue->fetchByKey( "b" )[0]->getData() );
        }

        public function testThatLockedQueueItemsWillNotBeRetrived() {

            $backend = new MemoryBackend;
            $queue   = new Queue( $backend );

            $a = new QueueItem( "a" );
            $b = new QueueItem( "b" );
            $c = new QueueItem( "c" );

            $queue->add( $a );
            $queue->add( $b );
            $queue->add( $c );

            $a->lock();
            $this->assertEquals( 2, count( $queue->fetchChannel() ) );

            $a->unlock();
            $this->assertEquals( 3, count( $queue->fetchChannel() ) );
        }

    }
