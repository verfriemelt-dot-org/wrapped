<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\SQL;

    use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
    use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

    trait Alias
    {
        protected ?Identifier $alias = null;

        public function addAlias(Identifier $ident): static
        {
            $this->alias = $ident;
            return $this;
        }

        public function as(Identifier $ident): static
        {
            return $this->addAlias($ident);
        }

        protected function stringifyAlias(DatabaseDriver $driver = null): string
        {
            return $this->alias ? " AS {$this->alias->stringify($driver)}" : '';
        }
    }
