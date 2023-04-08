<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use Exception;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Alias;
use verfriemelt\wrapped\_\Database\SQL\Aliasable;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use verfriemelt\wrapped\_\DataModel\DataModel;

class Identifier extends QueryPart implements ExpressionItem, Aliasable
{
    use Alias;

    protected $parts = [];

    public function __construct(...$parts)
    {
        // filter out null values
        $parts = array_filter($parts, fn ($p) => !is_null($p));

        // validation
        if (count($parts) === 0 || count($parts) > 3) {
            throw new Exception('illegal identifier to much or less identifier');
        }

        foreach ($parts as $part) {
            if (strlen((string) $part) === 0) {
                throw new Exception('illegal identifier');
            }
        }

        $this->parts = array_values($parts);
    }

    public function quote(string $ident, DatabaseDriver $driver = null): string
    {
        if (!$driver) {
            return $ident;
        }

        return $driver->quoteIdentifier($ident);
    }

    protected function translateField(string $ident, string $table = null)
    {
        if ($ident === '*' || count($this->context) === 0) {
            return $ident;
        }

        $translations = array_map(function (DataModel $context) use ($ident, $table) {
            try {
                if ($table !== null && $context->fetchTablename() !== $table) {
                    return null;
                }

                return $context::translateFieldName($ident);
            } catch (Exception) {
                return null;
            }
        }, $this->context);

        // filter null values;
        $translations = array_values(array_filter($translations));

        return match (count($translations)) {
            0 => $ident,
            1 => $translations[0]->fetchBackendName(),
            default => throw new Exception("field ambiguous: {$ident}"),
        };
    }

    public function stringify(DatabaseDriver $driver = null): string
    {
        $parts = [];

        switch (count($this->parts)) {
            case 3:
                [$schema, $table, $column] = $this->parts;

                $parts = [
                    $schema,
                    $table,
                    $this->translateField($column, $table),
                ];

                break;
            case 2:
                [$table, $column] = $this->parts;

                $parts = [
                    $table,
                    $this->translateField($column, $table),
                ];

                break;
            case 1:
                [$column] = $this->parts;

                $parts = [
                    $this->translateField($column),
                ];

                break;
        }

        return implode(
            '.',
            array_map(
                fn (string $p) => $p !== '*' ? $this->quote($p, $driver) : '*',
                $parts
            )
        ) . $this->stringifyAlias($driver);
    }
}
