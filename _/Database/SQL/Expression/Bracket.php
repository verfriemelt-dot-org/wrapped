<?php

    namespace Wrapped\_\Database\SQL\Expression;

    class Bracket
    extends Expression {

        public function stringify(): string {

            return "( ". parent::stringify()." )";

        }

    }
