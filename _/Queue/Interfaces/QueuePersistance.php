<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Queue\Interfaces;

    use \Wrapped\_\Queue\Queue;
    use \Wrapped\_\Queue\QueueItem;

    interface QueuePersistance {

        /**
         * Stores Item on the Queue
         * @param QueueItem $item
         */
        public function store( QueueItem $item );

        /**
         * Retrives items from the queue which are sorted from oldest to youngest and not locked
         * @param string $channel
         */
        public function fetchChannel( string $channel = Queue::DEFAULT_CHANNEL, int $limit = null ): array;

        /**
         * Retrives items from the queue which are sorted from oldest to youngest and not locked
         * filtered by key
         * @param string $channel
         */
        public function fetchByKey( string $key, string $channel = Queue::DEFAULT_CHANNEL, int $limit = null ): array;

        /**
         * removes items off the queue
         * @param QueueItem $item
         */
        public function deleteItem( QueueItem $item ): bool;

        /**
         * lock items on the queue, that they will not be retrived anymore
         * @param QueueItem $item
         */
        public function lock( QueueItem $item ): bool;

        /**
         * unlock item
         * @param QueueItem $item
         */
        public function unlock( QueueItem $item ): bool;

        /**
         * remove all entries off the queue
         */
        public function purge(): bool;
    }
