<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

interface VariableFormatter
{
    public function supports(string $name): bool;

    public function format(string $input): string;
}
