<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\SQL\Expression\Identifier;

    trait Alias {

        protected ?Identifier $alias = null;

        public function addAlias( Identifier $ident ): static {
            $this->alias = $ident;
            return $this;
        }

        protected function stringifyAlias( DatabaseDriver $driver = null ): string {
            return $this->alias ? " AS {$this->alias->stringify( $driver )}" : '';
        }

    }
    