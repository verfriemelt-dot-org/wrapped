<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

interface TablenameOverride
{
    public static function fetchTablename(): string;
}
