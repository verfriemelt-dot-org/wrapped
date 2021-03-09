<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel;

    interface PropertyObjectInterface {

        /**
         * creates property from stored value
         * @param type $storedValue
         */
        public static function hydrateFromString( $storedValue );

        /**
         * returns the object as string
         * @return string
         */
        public function dehydrateToString(): string;
    }
