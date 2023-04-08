<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Attribute\Naming;

use Exception;

abstract class Convention
{
    protected string $string;

    public const DESTRUCTIVE = false;

    final public function __construct(?string $str = null)
    {
        if ($str !== null) {
            $this->setString($str);
        }
    }

    public function setString(string $str)
    {
        $this->string = $str;
        return $this;
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function convertTo($class)
    {
        if (static::DESTRUCTIVE) {
            throw new Exception('not possible');
        }

        return $class::fromStringParts(...$this->fetchStringParts());
    }

    abstract public function fetchStringParts(): array;

    abstract public static function fromStringParts(string ...$parts): Convention;
}
