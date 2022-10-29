<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Queue\Backend\MemoryBackend;
use verfriemelt\wrapped\_\Queue\Queue;
use verfriemelt\wrapped\_\Queue\QueueItem;

class QueueTest extends TestCase
{
    public function testAddingQueueItem(): void
    {
        $queue = new Queue(new MemoryBackend());
        $queue->add(new QueueItem('testing'));
        $queue->add(new QueueItem('testing'));

        $result = $queue->fetchByKey('testing');

        foreach ($result as $queueItem) {
            static::assertTrue($queueItem instanceof QueueItem);
        }
    }

    public function testEmptyQueue(): void
    {
        $queue = new Queue(new MemoryBackend());

        static::assertSame([], $queue->fetchByKey('testing'));
    }

    public function testQueueSepartion(): void
    {
        $queue = new Queue(new MemoryBackend());
        $queue->add(new QueueItem('testing'));
        $queue->add(new QueueItem('testing'));
        $queue->add(new QueueItem('b'));
        $queue->add(new QueueItem('testing', 'email'));
        $queue->add(new QueueItem('b', 'email'));

        static::assertSame(2, count($queue->fetchByKey('testing')));
        static::assertSame(1, count($queue->fetchByKey('b')));
        static::assertSame(1, count($queue->fetchByKey('testing', 'email')));
        static::assertSame(1, count($queue->fetchByKey('b', 'email')));
    }

    public function testDeleteItemFromQueue(): void
    {
        $backend = new MemoryBackend();
        $queue = new Queue($backend);
        $queue->add(new QueueItem('b'));

        static::assertSame(1, count($queue->fetchByKey('b')));

        foreach ($queue->fetchByKey('b') as $item) {
            $item->delete();
        }

        static::assertSame([], $queue->fetchByKey('b'));
    }

    public function testAlterQueueItem(): void
    {
        $backend = new MemoryBackend();
        $queue = new Queue($backend);

        $queue->add(new QueueItem('b'));

        foreach ($queue->fetchByKey('b') as $item) {
            $item->setData(5);
        }

        static::assertSame(5, $queue->fetchByKey('b')[0]->getData());
    }

    public function testThatLockedQueueItemsWillNotBeRetrived(): void
    {
        $backend = new MemoryBackend();
        $queue = new Queue($backend);

        $a = new QueueItem('a');
        $b = new QueueItem('b');
        $c = new QueueItem('c');

        $queue->add($a);
        $queue->add($b);
        $queue->add($c);

        $a->lock();
        static::assertSame(2, count($queue->fetchChannel()));

        $a->unlock();
        static::assertSame(3, count($queue->fetchChannel()));
    }
}
