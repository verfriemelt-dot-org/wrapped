<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Queue;

    use \Wrapped\_\Queue\Backend\MysqlBackend;
    use \Wrapped\_\Queue\Interfaces\QueuePersistance;

    class Queue {

        const DEFAULT_CHANNEL = "default";

        private $storage = null;

        public function __construct( QueuePersistance $storage = null ) {
            $this->storage = $storage ?? new MysqlBackend;
        }

        /**
         * adds items to specifiyed channel
         * if no channel is set, than it will be "default"
         * @param QueueItem $item
         * @param type $channel
         * @return Queue
         */
        public function add( QueueItem $item, $channel = self::DEFAULT_CHANNEL ): Queue {

            $item->setQueue( $this );
            $this->storage->store( $item );

            return $this;
        }

        public function lock( QueueItem $item ): bool {
            $this->storage->lock( $item );
            return true;
        }

        public function unlock( QueueItem $item ): bool {
            $this->storage->unlock( $item );
            return true;
        }

        public function fetchByUniqueId( $uniqueId ) {
            return $this->storage->fetchByUniqueId( $uniqueId );
        }

        /**
         *
         * @param type $key
         * @param type $channel
         * @param int $limit
         * @return QueueItem[]
         */
        public function fetchByKey( $key, $channel = self::DEFAULT_CHANNEL, int $limit = null ) {
            return $this->storage->fetchByKey( $key, $channel, $limit );
        }

        /**
         *
         * @param type $channel
         * @param int $limit
         * @return QueueItem[]
         */
        public function fetchChannel( $channel = self::DEFAULT_CHANNEL, int $limit = null ) {
            return $this->storage->fetchChannel( $channel, $limit );
        }

        /**
         * removes specified item from queue
         * @param QueueItem $item
         * @return bool
         */
        public function removeQueueItem( QueueItem $item ): bool {
            return $this->storage->deleteItem( $item );
        }

    }
