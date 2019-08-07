<?php

    namespace Wrapped\_\DataModel\CacheHelper;

    use \Wrapped\_\Cache\CacheFactory;

    trait DataModelCaching {

        protected static function storeInCache( $instance, string $key ): bool {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            $cache = CacheFactory::getCache();
            return $cache->set( static::class . $key, serialize( $instance ), 600 );
        }

        protected static function retriveFromCache( string $key ) {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            if ( $data = CacheFactory::getCache()->get( static::class . $key ) ) {
                return unserialize( $data );
            }

            return false;
        }

        protected static function deleteFromCache( string $key ) {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            $cache = CacheFactory::getCache();
            $cache->delete( static::class . $key );
        }

        public function save() {

            $result = parent::save();
            static::storeInCache( $this, $this->{static::_fetchPrimaryKey()} );

            return $result;
        }

        public static function get( $id ) {

            $instance = static::retriveFromCache( $id );

            if ( !$instance ) {
                $instance = parent::get( $id );
                static::storeInCache( $instance, $id );
            }

            return $instance;
        }

        public static function fetchBy( string $field, $value ) {

            // mapping
            $pk = static::retriveFromCache( $field . (string) $value );

            if ( !$pk ) {

                $instance = parent::fetchBy( $field, $value );

                static::storeInCache( $instance, $instance->{static::_fetchPrimaryKey()} );
                static::storeInCache( $pk, $field . (string) $value );

                return $instance;
            }

            return static::get( $pk );
        }

        public function delete() {
            static::deleteFromCache( $this->{static::_fetchPrimaryKey()} );
            parent::delete();
        }

    }
