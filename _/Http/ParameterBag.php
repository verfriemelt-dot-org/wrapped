<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http;

    use ArrayIterator;
    use Countable;
    use IteratorAggregate;
    use Traversable;

    class ParameterBag implements Countable, IteratorAggregate
    {
        /**
         * @var mixed[]
         */
        private $parameters = [];

        private ?string $raw = null;

        public function __construct(array $parameters)
        {
            $this->parameters = $parameters;
        }

        public function count(): int
        {
            return count($this->parameters);
        }

        /**
         * @return ArrayIterator
         */
        public function getIterator(): Traversable
        {
            return new ArrayIterator($this->parameters);
        }

        public function hasNot(string $param): bool
        {
            return !$this->has($param);
        }

        public function has(string $key): bool
        {
            return isset($this->parameters[$key]);
        }

        public function get(string $key, string|int $default = null)
        {
            if (!$this->has($key)) {
                return $default;
            }

            return $this->parameters[$key];
        }

        public function is(string $key, string|int $value)
        {
            return $this->get($key) === $value;
        }

        public function isNot(string $key, string|int $value)
        {
            return $this->get($key) !== $value;
        }

        /**
         * @return mixed[]
         */
        public function all(): array
        {
            return $this->parameters;
        }

        public function first(): mixed
        {
            reset($this->parameters);
            return current($this->parameters);
        }

        public function last(): mixed
        {
            end($this->parameters);
            return current($this->parameters);
        }

        /**
         * @param mixed[] $filter
         *
         * @return mixed[]
         */
        public function except(array $filter = []): array
        {
            $return = [];

            foreach ($this->all() as $key => $value) {
                if (!in_array($key, $filter, true)) {
                    $return[$key] = $value;
                }
            }

            return $return;
        }

        public function override(string|int $key, mixed $value = null): static
        {
            $this->parameters[$key] = $value;
            return $this;
        }

        public function setRawData(string $content): static
        {
            $this->raw = $content;
            return $this;
        }

        public function getRawData(): ?string
        {
            return $this->raw;
        }
    }
