<?php

    namespace Wrapped\_\DataModel\Tree;

    use \Wrapped\_\DataModel\Collection;
    use \Wrapped\_\DataModel\Tree\TreeDataModelInterface;

    interface TreeDataModelInterface {

        public function fetchParent(): ?static;

        public function fetchChildCount(): int;

        public function isChildOf( TreeDataModelInterface $model ): bool;

        public function fetchDirectChildren( $order = "left", $direction = "ASC" ): Collection;

        public function fetchChildrenInclusive( $order = "left", $direction = "ASC", int $depth = null ): Collection;

        public function fetchChildren( $order = "left", $direction = "ASC", int $depth = null ): Collection;

        public function fetchPath(): Collection;

        public function move(): static;

        public function under( TreeDataModelInterface $parent ): static;
    }
