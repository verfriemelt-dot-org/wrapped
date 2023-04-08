<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\Driver;

use PDO;

class Mysql extends DatabaseDriver
{
    final public const PDO_NAME = 'mysql';

    public function quoteIdentifier(string $ident): string
    {
        return "`{$ident}`";
    }

    public function enableUnbufferedMode($bool = true)
    {
        $this->connectionHandle->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, !$bool);
        return $this;
    }
}
