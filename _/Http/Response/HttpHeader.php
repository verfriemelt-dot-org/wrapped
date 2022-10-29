<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Response;

    class HttpHeader
    {
        private string $name;

        private string $value;

        private bool $replaces = true;

        public function __construct(string $name, string $value)
        {
            $this->name = $name;
            $this->value = $value;
        }

        public function replace($bool = true): static
        {
            $this->replaces = $bool;
            return $this;
        }

        public function replaces(): bool
        {
            return $this->replaces;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function getValue(): string
        {
            return $this->value;
        }

        public function setName($name): static
        {
            $this->name = $name;
            return $this;
        }

        public function setValue($value): static
        {
            $this->value = $value;
            return $this;
        }
    }
