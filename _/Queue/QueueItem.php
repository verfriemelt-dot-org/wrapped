<?php

    namespace Wrapped\_\Queue;

    use \Wrapped\_\DataModel\DateTimeHandler;

    class QueueItem {

        use DateTimeHandler;

        public $channel  = Queue::DEFAULT_CHANNEL;
        public $key;
        public $uniqId;
        public $priority = 100;
        public $startDate;
        public $locked   = false;
        public $data;

        public function __construct( $key, $channel = null ) {
            $this->key     = $key;
            $this->channel = $channel ?? "default";
            $this->uniqId  = md5(uniqid( rand() ) . uniqid());
        }

        public function setQueue( Queue $queue ): QueueItem {
            $this->queue = $queue;
            return $this;
        }

        public function setData( $data ): QueueItem {
            $this->data = $data;
            return $this;
        }

        public function setPriority( int $priority ): QueueItem {
            $this->priority = $priority;
            return $this;
        }

        public function getData() {
            return $this->data;
        }

        public function setStartDate( $startDate ) {
            $this->startDate = $this->dateTimeToMysql( $startDate );
            return $this;
        }

        public function delete() {
            $this->queue->removeQueueItem( $this );
        }

        /**
         * locks item on the queue
         * locked items are not retrived by the queuebackend
         * @return \Wrapped\_\Queue\QueueItem
         */
        public function lock(): QueueItem {
            $this->queue->lock( $this );
            $this->locked = true;
            return $this;
        }

        /**
         * unlocks item on the queue
         * @return \Wrapped\_\Queue\QueueItem
         */
        public function unlock(): QueueItem {
            $this->queue->unlock( $this );
            $this->locked = false;
            return $this;
        }

    }
