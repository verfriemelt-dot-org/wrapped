<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Queue\Interfaces;

use verfriemelt\wrapped\_\Queue\Queue;
use verfriemelt\wrapped\_\Queue\QueueItem;

interface QueuePersistance
{
    /**
     * Stores Item on the Queue
     */
    public function store(QueueItem $item);

    /**
     * Retrives items from the queue which are sorted from oldest to youngest and not locked
     */
    public function fetchChannel(string $channel = Queue::DEFAULT_CHANNEL, int $limit = null): array;

    /**
     * Retrives items from the queue which are sorted from oldest to youngest and not locked
     * filtered by key
     */
    public function fetchByKey(string $key, string $channel = Queue::DEFAULT_CHANNEL, int $limit = null): array;

    /**
     * removes items off the queue
     */
    public function deleteItem(QueueItem $item): bool;

    /**
     * lock items on the queue, that they will not be retrived anymore
     */
    public function lock(QueueItem $item): bool;

    /**
     * unlock item
     */
    public function unlock(QueueItem $item): bool;

    /**
     * remove all entries off the queue
     */
    public function purge(): bool;
}
