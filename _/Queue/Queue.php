<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Queue;

    use verfriemelt\wrapped\_\Queue\Backend\MysqlBackend;
    use verfriemelt\wrapped\_\Queue\Interfaces\QueuePersistance;

    class Queue
    {
        public const DEFAULT_CHANNEL = 'default';

        private QueuePersistance $storage;

        public function __construct(QueuePersistance $storage = null)
        {
            $this->storage = $storage ?? new MysqlBackend();
        }

        /**
         * adds items to specifiyed channel
         * if no channel is set, than it will be "default"
         *
         * @param type $channel
         */
        public function add(QueueItem $item, $channel = self::DEFAULT_CHANNEL): Queue
        {
            $item->setQueue($this);
            $this->storage->store($item);

            return $this;
        }

        public function lock(QueueItem $item): bool
        {
            $this->storage->lock($item);
            return true;
        }

        public function unlock(QueueItem $item): bool
        {
            $this->storage->unlock($item);
            return true;
        }

        public function fetchByKey(string $key, string $channel = self::DEFAULT_CHANNEL, int $limit = null): array
        {
            return $this->storage->fetchByKey($key, $channel, $limit);
        }

        public function fetchChannel(string $channel = self::DEFAULT_CHANNEL, int $limit = null): array
        {
            return $this->storage->fetchChannel($channel, $limit);
        }

        /**
         * removes specified item from queue
         */
        public function removeQueueItem(QueueItem $item): bool
        {
            return $this->storage->deleteItem($item);
        }
    }
