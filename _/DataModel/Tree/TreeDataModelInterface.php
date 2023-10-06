<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\Tree;

use verfriemelt\wrapped\_\DataModel\Collection;

interface TreeDataModelInterface
{
    public function fetchParent(): ?static;

    public function fetchChildCount(): int;

    public function isChildOf(TreeDataModelInterface $model): bool;

    /**
     * @return Collection<TreeDataModelInterface>
     */
    public function fetchDirectChildren(string $order = 'left', string $direction = 'ASC'): Collection;

    /**
     * @return Collection<TreeDataModelInterface>
     */
    public function fetchChildrenInclusive(string $order = 'left', string $direction = 'ASC', ?int $depth = null): Collection;

    /**
     * @return Collection<TreeDataModelInterface>
     */
    public function fetchChildren(string $order = 'left', string $direction = 'ASC', ?int $depth = null): Collection;

    /**
     * @return Collection<TreeDataModelInterface>
     */
    public function fetchPath(): Collection;

    public function move(): TreeDataModelInterface;

    public function under(TreeDataModelInterface $parent): TreeDataModelInterface;
}
