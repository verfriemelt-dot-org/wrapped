<?php namespace Wrapped\_\DataModel;

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
