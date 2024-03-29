<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command\CommandArguments;

final class Argument
{
    final public const int VARIADIC = 0b10;
    final public const int REQUIRED = 0b01;
    final public const int OPTIONAL = 0b00;

    private ?string $value = null;
    private bool $isPresent = false;

    public function __construct(
        public readonly string $name,
        public readonly int $flags = self::REQUIRED,
        public readonly ?string $description = null,
        public readonly ?string $default = null,
    ) {
        if ($this->required() && $this->default !== null) {
            throw new ArgumentDefinitionError('cannot mix required arguments with default value');
        }
    }

    public function required(): bool
    {
        return ($this->flags & self::REQUIRED) === self::REQUIRED;
    }

    public function variadic(): bool
    {
        return ($this->flags & self::VARIADIC) === self::VARIADIC;
    }

    /**
     * @phpstan-assert-if-true !null $this->get()
     */
    public function present(): bool
    {
        return $this->isPresent;
    }

    public function get(): ?string
    {
        return $this->value ?? $this->default;
    }

    public function set(string $value): void
    {
        $this->isPresent = true;
        $this->value =  $value;
    }
}
