<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;
use RuntimeException;

final class ParameterBag implements Countable, IteratorAggregate
{
    /**
     * @param array<int|string, mixed> $parameters
     */
    public function __construct(
        private array $parameters = []
    ) {}

    #[Override]
    public function count(): int
    {
        return count($this->parameters);
    }

    /**
     * @return ArrayIterator
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->parameters);
    }

    public function hasNot(string|int $param): bool
    {
        return !$this->has($param);
    }

    public function has(string|int $key): bool
    {
        return isset($this->parameters[$key]);
    }

    public function get(string|int $key, ?string $default = null): ?string
    {
        if (!$this->has($key)) {
            return $default;
        }

        if (!\is_scalar($this->parameters[$key])) {
            throw new RuntimeException('non-scalar element requested');
        }

        return (string) $this->parameters[$key];
    }

    /**
     * @return array<int|string, mixed>
     */
    public function all(): array
    {
        return $this->parameters;
    }

    public function first(): ?string
    {
        return $this->get(0);
    }

    public function last(): ?string
    {
        return $this->get(array_key_last($this->parameters));
    }
}
