<?php

    namespace Wrapped\_\DataModel\CacheHelper;

    trait MemoryCache {

        public static $_inMemoryObejectCache = [];

        /**
         * stores the fetched instance in memory for reducing databaseload
         * be aware, that this could create issues with updating models and reusing them afterswards
         * do not use this with treeDataModels
         * @param type $id
         * @return static
         */
        public static function get( $id ) {

            if ( isset( static::$_inMemoryObejectCache[$id] ) ) {
                return static::$_inMemoryObejectCache[$id];
            }

            static::$_inMemoryObejectCache[$id] = parent::get( $id );
            return static::$_inMemoryObejectCache[$id];
        }

        /**
         * removes the cached memory version for clean delete
         * @return
         */
        public function delete() {

            if ( isset( static::$_inMemoryObejectCache[$this->getId()] ) ) {
                unset( static::$_inMemoryObejectCache[$this->getId()] );
            }

            return parent::delete();
        }

    }
    