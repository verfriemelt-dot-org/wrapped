<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

class SelectGroup
{
    public readonly string $name;

    /**
     * @var SelectItem[]
     */
    private array $children = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function addChild(SelectItem $item): self
    {
        $this->children[] = $item;
        return $this;
    }

    /**
     * @return SelectItem[]
     */
    public function fetchChildren(): array
    {
        return $this->children;
    }
}
