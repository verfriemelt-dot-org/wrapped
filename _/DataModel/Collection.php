<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use JsonSerializable;
use OutOfBoundsException;
use PDOStatement;
use SeekableIterator;
use verfriemelt\wrapped\_\Database\Facade\QueryBuilder;

/**
 * @template T of DataModel
 */
class Collection implements Iterator, ArrayAccess, Countable, SeekableIterator, JsonSerializable
{
    /**
     * @var int<0,max>
     */
    private int $length = 0;

    /**
     * @var int<0,max>
     */
    private int $pointer = 0;

    /**
     * @var T[]
     */
    private array $data = [];

    private $loadMoreCallback;

    /**
     * @param T ...$data
     */
    final public function __construct(DataModel ...$data)
    {
        $this->initialize(...$data);
    }

    public static function buildFromQuery(DataModel $prototype, QueryBuilder $query)
    {
        return static::buildFromPdoResult($prototype, $query->run());
    }

    /**
     * @param T $prototype
     *
     * @return Collection<T>
     */
    public static function buildFromPdoResult(DataModel $prototype, PDOStatement $result): Collection
    {
        $collection = new static();
        $instances = [];

        while ($data = $result->fetch()) {
            $instances[] = ( new $prototype() )->initData($data);
        }

        return $collection->initialize(...$instances);
    }

    public function setLoadingCallback(callable $func): self
    {
        $this->loadMoreCallback = $func;
        return $this;
    }

    public function setLength(int $lenght): self
    {
        if ($lenght < 0) {
            throw new \RuntimeException('length must by >= 0');
        }

        $this->length = $lenght;
        return $this;
    }

    /**
     * @param T ...$data
     *
     * @return $this
     */
    public function initialize(DataModel ...$data): self
    {
        $this->data = $data;
        $this->length = count($data);

        return $this;
    }

    /**
     * countable implemententation
     *
     * @return int<0,max>
     */
    public function count(): int
    {
        return $this->length;
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * @return ?T
     */
    public function current(): ?DataModel
    {
        if ($this->valid()) {
            return $this->offsetGet($this->pointer);
        }

        return null;
    }

    public function key(): int
    {
        return $this->pointer;
    }

    public function next(): void
    {
        ++$this->pointer;
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function valid(): bool
    {
        return $this->pointer < $this->length;
    }

    /**
     * @return T|null
     */
    public function last(): ?DataModel
    {
        if ($this->count() === 0) {
            return null;
        }

        return $this->offsetGet($this->count() - 1);
    }

    /**
     * @return T|null
     */
    public function first(): ?DataModel
    {
        if ($this->count() == 0) {
            return null;
        }

        return $this->offsetGet(0);
    }

    /**
     * @param int $offset
     *
     * @throws Exception
     */
    public function offsetExists(mixed $offset): bool
    {
        if ($offset < 0 || $offset >= $this->length) {
            throw new Exception("illegal offset {$offset} in result");
        }

        // todo load more results
        if (!isset($this->data[$offset])) {
            // data loading
            $obj = ($this->loadMoreCallback)($offset);

            if (!$obj) {
                throw new Exception("unable to fetch offset {$offset}");
            }

            $this->data[$offset] = $obj;
        }

        return isset($this->data[$offset]);
    }

    /**
     * @param int $offset
     *
     * @return T
     *
     * @throws Exception
     */
    public function offsetGet(mixed $offset): DataModel
    {
        // validate offset
        $this->offsetExists($offset);

        return $this->data[$offset];
    }

    /**
     * array access implementation
     * disabled
     *
     * @param int    $offset
     * @param T|null $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('write only collections');
    }

    /**
     * array access implementation
     * disabled
     *
     * @param int $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('write only collections');
    }

    public function seek(int $position): void
    {
        if ($position >= $this->length || $position < 0) {
            throw new OutOfBoundsException();
        }

        $this->pointer = $position;
    }

    /**
     * @return mixed[]
     */
    public function map(callable $callable): array
    {
        $result = [];

        foreach ($this as $element) {
            $result[] = $callable($element);
        }

        return $result;
    }

    public function call(callable $function): self
    {
        array_map($function, $this->data);
        return $this;
    }

    /**
     * @return Collection<T>
     */
    public function filter(callable $function): Collection
    {
        return new static(...array_filter($this->data, $function));
    }

    /**
     * @return Collection<T>
     */
    public function reverse(): Collection
    {
        return new static(...array_reverse($this->data));
    }

    public function reduce(callable $function, $initial = null): mixed
    {
        return array_reduce($this->data, $function, $initial);
    }

    /**
     * @return Collection<T>
     */
    public function sort(callable $function): Collection
    {
        $copy = $this->data;
        usort($copy, $function);

        return new static(...$copy);
    }

    /**
     * @return T|null
     */
    public function find(callable $function): ?DataModel
    {
        foreach ($this as $element) {
            if ($function($element)) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @return array<T>
     */
    public function toArray(): mixed
    {
        return $this->data;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
