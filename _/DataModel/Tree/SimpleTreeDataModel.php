<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Tree;

use Exception;
use verfriemelt\wrapped\_\Database\SQL\Clause\CTE;
use verfriemelt\wrapped\_\Database\SQL\Clause\From;
use verfriemelt\wrapped\_\Database\SQL\Clause\Join;
use verfriemelt\wrapped\_\Database\SQL\Clause\Union;
use verfriemelt\wrapped\_\Database\SQL\Clause\Where;
use verfriemelt\wrapped\_\Database\SQL\Expression\Cast;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\DataModel\Collection;
use verfriemelt\wrapped\_\DataModel\DataModel;
use Override;

abstract class SimpleTreeDataModel extends DataModel implements TreeDataModelInterface
{
    public static function getParentProperty(): string
    {
        return 'parentId';
    }

    public static function getRereferencedParentProperty(): string
    {
        return static::getPrimaryKey();
    }

    #[Override]
    public function fetchChildCount(): int
    {
        return $this->fetchChildren()->count();
    }

    #[Override]
    public function fetchChildren(string $order = 'left', string $direction = 'ASC', ?int $depth = null): Collection
    {
        $parentProp = static::createDataModelAnalyser()->fetchPropertyByName(static::getParentProperty());
        $primaryProp = static::createDataModelAnalyser()->fetchPropertyByName(static::getRereferencedParentProperty());

        $cte = new CTE();
        $cte->recursive();

        $recursiveStatement = static::buildSelectQuery()->stmt;
        $recursiveStatement->getCommand()->add(
            (new Expression(
                new Value(1),
                new Cast('int'),
            ))->as(
                new Identifier('_depth'),
            ),
        );

        $lowerSelect = static::buildSelectQuery()->stmt->getCommand();
        $lowerSelect->add(
            new Expression(
                new Identifier('_depth'),
                new Cast('int'),
                new Operator('+'),
                new Value(1),
            ),
        );

        $cte->with(
            new Identifier('_data'),
            $recursiveStatement
                ->add(
                    new Where(
                        new Expression(
                            new Identifier($parentProp->fetchBackendName()),
                            new Operator('='),
                            new Value($this->{$primaryProp->getGetter()}()),
                        ),
                    ),
                )
                ->add(new Union())
                ->add($lowerSelect)
                ->add(new From(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
                ->add(
                    new Join(
                        new Identifier('_data'),
                        new Expression(
                            new Identifier(
                                static::fetchSchemaname(),
                                static::fetchTablename(),
                                $parentProp->fetchBackendName(),
                            ),
                            new Operator('='),
                            new Identifier('_data', $primaryProp->fetchBackendName()),
                        ),
                    ),
                ),
        );

        if ($depth !== null) {
            $recursiveStatement->add(
                new Where(new Expression(new Identifier('_depth'), new Operator('<'), new Value($depth))),
            );
        }

        $query = static::buildQuery();
        $query->stmt->add($cte);

        $query->select(...array_map(fn ($i) => $i[1], static::fetchSelectColumns()));
        $query->from('_data');

        $query->addContext($this);

        return $query->get();
    }

    #[Override]
    public function fetchChildrenInclusive(string $order = 'left', string $direction = 'ASC', ?int $depth = null): Collection
    {
        return new Collection([
            $this,
            ...$this->fetchChildren($order, $direction, $depth),
        ]);
    }

    #[Override]
    public function fetchDirectChildren(string $order = 'left', string $direction = 'ASC'): Collection
    {
        return $this->fetchChildren($order, $direction, depth: 1);
    }

    #[Override]
    public function fetchParent(): ?static
    {
        $parentProp = static::createDataModelAnalyser()->fetchPropertyByName(static::getParentProperty());
        $referencedProperty = static::createDataModelAnalyser()->fetchPropertyByName(
            static::getRereferencedParentProperty(),
        );
        $parent = $this->{$parentProp->getGetter()}();

        if ($parent === null) {
            return null;
        }

        return static::fetchBy($referencedProperty->getName(), $parent);
    }

    #[Override]
    public function fetchPath(): Collection
    {
        return new Collection();
    }

    #[Override]
    public function isChildOf(TreeDataModelInterface $model): bool
    {
        return false;
    }

    #[Override]
    public function move(): static
    {
        return $this;
    }

    #[Override]
    public function under(TreeDataModelInterface $parent): static
    {
        if (!($parent instanceof $this)) {
            throw new Exception('cannot mix models');
        }

        $parentProp = static::createDataModelAnalyser()->fetchPropertyByName(static::getParentProperty());
        $referencedProperty = static::createDataModelAnalyser()->fetchPropertyByName(
            static::getRereferencedParentProperty(),
        );

        $this->{$parentProp->getSetter()}($parent->{$referencedProperty->getGetter()}());

        return $this;
    }
}
