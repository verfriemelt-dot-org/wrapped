<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Queue\Backend;

use verfriemelt\wrapped\_\Queue\Interfaces\QueuePersistance;
use verfriemelt\wrapped\_\Queue\Queue;
use verfriemelt\wrapped\_\Queue\QueueItem;

class MysqlBackend implements QueuePersistance
{
    private readonly MysqlBackendDataObject $storage;

    public function __construct(MysqlBackendDataObject $storage = null)
    {
        $this->storage = $storage ?? new MysqlBackendDataObject();
    }

    public function deleteItem(QueueItem $item): bool
    {
        $instance = $this->storage::findSingle(['uniqId' => $item->uniqId]);

        if (!$instance) {
            return false;
        }

        $instance->delete();
        return true;
    }

    public function fetchByUniqueId($uniqueId)
    {
        $item = $this->storage::findSingle(['uniqId' => $uniqueId]);
        return $item ? $item->read() : null;
    }

    public function fetchByKey(string $key, string $channel = Queue::DEFAULT_CHANNEL, int $limit = null): array
    {
        $collection = $this->storage::find(['channel' => $channel, 'locked' => 0, 'key' => $key], 'date');
        $queueItems = $collection->map(fn (MysqlBackendDataObject $i) => $i->read());

        return $queueItems;
    }

    public function fetchChannel(string $channel = Queue::DEFAULT_CHANNEL, int $limit = null): array
    {
        $collection = $this->storage::find(['channel' => $channel, 'locked' => 0], 'date');
        $queueItems = $collection->map(fn (MysqlBackendDataObject $i) => $i->read());

        return $queueItems;
    }

    public function purge(): bool
    {
        return false;
    }

    public function store(QueueItem $item)
    {
        $backend = new MysqlBackendDataObject();
        $backend->write($item);
        $backend->save();

        return $this;
    }

    public function lock(QueueItem $item): bool
    {
        $item = $this->storage::findSingle(['uniqId' => $item->uniqId]);

        if (!$item) {
            return false;
        }

        $item->lock();
        return true;
    }

    public function unlock(QueueItem $item): bool
    {
        $item = $this->storage::findSingle(['uniqId' => $item->uniqId]);

        if (!$item) {
            return false;
        }

        $item->unlock();
        return true;
    }
}
