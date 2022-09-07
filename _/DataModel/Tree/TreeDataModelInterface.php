<?php

namespace verfriemelt\wrapped\_\DataModel\Tree;

use \verfriemelt\wrapped\_\DataModel\Collection;

interface TreeDataModelInterface
{

    public function fetchParent(): ?static;

    public function fetchChildCount(): int;

    public function isChildOf( TreeDataModelInterface $model ): bool;

    public function fetchDirectChildren( $order = "left", $direction = "ASC" ): Collection;

    public function fetchChildrenInclusive( $order = "left", $direction = "ASC", int $depth = null ): Collection;

    public function fetchChildren( $order = "left", $direction = "ASC", int $depth = null ): Collection;

    public function fetchPath(): Collection;

    public function move(): TreeDataModelInterface;

    public function under( TreeDataModelInterface $parent ): TreeDataModelInterface;
}
