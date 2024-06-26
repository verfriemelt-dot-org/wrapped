<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

use verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\Convention;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\Rename;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;

class DataModelProperty
{
    private readonly string $name;

    private string $setter;

    private string $getter;

    private readonly bool $isNullable;

    private readonly Convention $case;

    private ?string $type = null;

    private Rename $renamed;

    public function __construct(
        string $name,
        bool $isNullable,
        ?Convention $case = null,
    ) {
        $this->name = $name;
        $this->isNullable = $isNullable;
        $this->case = (new CamelCase($name))->convertTo($case ?? SnakeCase::class);
    }

    public function isRenamed(): bool
    {
        return isset($this->renamed);
    }

    public function setRenamed(Rename $renamed)
    {
        $this->renamed = $renamed;
        return $this;
    }

    public function fetchBackendName(): string
    {
        if ($this->isRenamed()) {
            return $this->renamed->name;
        }

        return $this->getNamingConvention()->getString();
    }

    public function getSetter(): string
    {
        return $this->setter;
    }

    public function getGetter(): string
    {
        return $this->getter;
    }

    public function setSetter(string $setter): void
    {
        $this->setter = $setter;
    }

    public function setGetter(string $getter): void
    {
        $this->getter = $getter;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getNamingConvention(): Convention
    {
        return $this->case;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isNamed(string $name): bool
    {
        return $this->name === $name || $this->case->getString() === $name;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
