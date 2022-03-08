<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel;

    interface PropertyObjectInterface {

        /**
         * creates property from stored value
         * @param ?string $storedValue
         */
        public static function hydrateFromString( ?string $storedValue ): ?static;

        /**
         * returns the object as string
         * @return string
         */
        public function dehydrateToString(): string;
    }
