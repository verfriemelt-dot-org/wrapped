<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

class SelectItem
{
    public function __construct(
        public readonly string $name,
        public readonly string $value
    ) {}
}
