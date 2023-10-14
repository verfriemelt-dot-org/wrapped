<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DataModel;

use Exception;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DataModel\Collection;
use verfriemelt\wrapped\_\DataModel\DataModel;

class CollectionDummy extends DataModel
{
    public int $id = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $offset): static
    {
        $this->id = $offset;
        return $this;
    }
}

class CollectionTest extends TestCase
{
    public function test_collection_init(): void
    {
        $collection = new Collection();
        static::assertSame(0, $collection->count());
        static::assertTrue($collection->isEmpty());
    }

    public function test_collection_length(): void
    {
        $collection = new Collection(...[
            new CollectionDummy(),
            new CollectionDummy(),
        ]);

        static::assertSame(2, $collection->count());
    }

    public function test_callback(): void
    {
        $callback = function ($offset): DataModel {
            if ($offset < 10) {
                return new CollectionDummy();
            }

            throw new Exception('empty');
        };

        $collection = new Collection();
        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        static::assertSame(10, $collection->count());

        $counter = 0;

        foreach ($collection as $instance) {
            static::assertTrue($instance instanceof CollectionDummy);
            ++$counter;
        }

        static::assertSame(10, $counter);
    }

    public function test_map(): void
    {
        $callback = function ($offset): DataModel {
            if ($offset < 10) {
                return new CollectionDummy();
            }

            throw new Exception('empty');
        };

        $collection = new Collection();
        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        static::assertSame(10, $collection->count());

        $result = $collection->map(fn (CollectionDummy $d): int => $d->getId());

        // iterate all
        static::assertSame(10, count($result));
    }

    public function test_array_access(): void
    {
        $callback = fn ($offset): DataModel => (new CollectionDummy())->setId($offset + 1);

        $collection = new Collection(new CollectionDummy());

        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        // 5th element in array should have id of 5
        static::assertSame(5, $collection[4]->getId());

        $this->expectExceptionObject(new Exception('illegal offset'));

        /* @phpstan-ignore-next-line */
        $collection[11];
    }

    public function test_start_end_getter(): void
    {
        /**
         * @var Collection<CollectionDummy> $collection
         */
        $collection = new Collection();

        static::assertNull($collection->last());
        static::assertNull($collection->first());

        $callback = fn ($offset): DataModel => (new CollectionDummy())->setId($offset + 1);

        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        // last element
        static::assertSame(10, $collection->last()?->getId());
        static::assertSame(1, $collection->first()?->getId());
    }

    public function test_seek(): void
    {
        $callback = fn ($offset): DataModel => (new CollectionDummy())->setId($offset + 1);

        $collection = new Collection(new CollectionDummy());
        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        $collection->seek(4);
        // 5th element in array should have id of 5
        static::assertSame(5, $collection->current()?->getId());

        $this->expectExceptionObject(new OutOfBoundsException());
        $collection->seek(11);
    }

    public function test_illegal_offget(): void
    {
        $callback = fn ($offset): DataModel => (new CollectionDummy())->setId($offset + 1);

        $collection = new Collection();
        $collection->setLength(10);
        $collection->setLoadingCallback($callback);

        $this->expectExceptionObject(new Exception('illegal offset'));

        // trigger exception
        /* @phpstan-ignore-next-line */
        $collection[-1];
    }

    public function test_illegal_offset(): void
    {
        $collection = new Collection(new CollectionDummy());

        $this->expectExceptionObject(new Exception('write only'));
        $collection[1] = new CollectionDummy();
    }

    public function test_illegal_offunset(): void
    {
        $collection = new Collection(new CollectionDummy());

        $this->expectExceptionObject(new Exception('write only'));
        unset($collection[1]);
    }
}
