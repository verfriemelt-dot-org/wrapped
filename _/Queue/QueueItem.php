<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Queue;

use verfriemelt\wrapped\_\DateTime\DateTime;

class QueueItem
{
    public $channel = Queue::DEFAULT_CHANNEL;

    public $key;

    public $uniqId;

    public int $priority = 100;

    public ?DateTime $startDate = null;

    public bool $locked = false;

    public $data;

    private Queue $queue;

    public function __construct($key, $channel = null)
    {
        $this->key = $key;
        $this->channel = $channel ?? 'default';
        $this->uniqId = md5(uniqid((string) random_int(0, mt_getrandmax())) . uniqid());
    }

    public function setQueue(Queue $queue): QueueItem
    {
        $this->queue = $queue;
        return $this;
    }

    public function setData($data): QueueItem
    {
        $this->data = $data;
        return $this;
    }

    public function setPriority(int $priority): QueueItem
    {
        $this->priority = $priority;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function delete()
    {
        $this->queue->removeQueueItem($this);
    }

    /**
     * locks item on the queue
     * locked items are not retrived by the queuebackend
     */
    public function lock(): QueueItem
    {
        $this->queue->lock($this);
        $this->locked = true;
        return $this;
    }

    /**
     * unlocks item on the queue
     */
    public function unlock(): QueueItem
    {
        $this->queue->unlock($this);
        $this->locked = false;
        return $this;
    }
}
