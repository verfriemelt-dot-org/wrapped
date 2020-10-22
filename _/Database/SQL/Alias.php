<?php

    namespace Wrapped\_\Database\SQL;

    use \Wrapped\_\Database\SQL\Expression\Identifier;

    trait Alias {

        protected ?Identifier $alias = null;

        public function addAlias( Identifier $ident ) {
            $this->alias = $ident;
            return $this;
        }

        protected function stringifyAlias(): string {
            return $this->alias ? " AS {$this->alias->stringify()}" : '';
        }

    }
