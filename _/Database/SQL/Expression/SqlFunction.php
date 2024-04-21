<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL\Expression;

use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\Database\SQL\Alias;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use Override;

class SqlFunction extends QueryPart implements ExpressionItem
{
    use Alias;

    final public const string SYNTAX = '%s( %s )';

    protected Identifier $name;
    protected Identifier $schema;

    protected array $arguments;

    public function __construct(Identifier $name, ExpressionItem ...$args)
    {
        $this->addChild($name);

        foreach ($args as $arg) {
            $this->addChild($arg);
        }

        $this->name = $name;
        $this->arguments = $args;
    }

    public function setSchema(Identifier $schema)
    {
        $this->schema = $schema;
        return $this;
    }

    #[Override]
    public function stringify(?DatabaseDriver $driver = null): string
    {
        // some functions are keywords
        $keywords = [
            'coalesce',
            'least',
            'greatest',
            'nullif',
        ];

        if (in_array($this->name->stringify(), $keywords)) {
            $name = $this->name->stringify(null);
        } else {
            $name = $this->name->stringify($driver);

            if (isset($this->schema)) {
                $name = $this->schema->stringify($driver) . '.' . $name;
            }
        }

        return sprintf(
            static::SYNTAX,
            $name,
            implode(', ', array_map(fn (ExpressionItem $i) => $i->stringify($driver), $this->arguments)),
        )
            . $this->stringifyAlias($driver);
    }
}
