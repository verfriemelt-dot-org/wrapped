<?php

    namespace Wrapped\_\DataModel\CacheHelper;

    use \Wrapped\_\Cache\CacheFactory;

    trait Memcached {

        protected static function storeInCache( $instance, $key ): bool {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            $cache = CacheFactory::getCache();
            return $cache->set( static::class . $key, serialize( $instance ), 600 );
        }

        protected static function retriveFromCache( $key ) {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            $cache = CacheFactory::getCache();
            $data  = $cache->get( static::class . $key );

            if ( $data ) {
                return unserialize( $data );
            }

            return false;
        }

        protected static function deleteFromCache( $key ) {

            if ( !CacheFactory::hasCache() ) {
                return false;
            }

            $cache = CacheFactory::getCache();
            $cache->delete( static::class . $key );
        }

        public function save() {

            $result = parent::save();
            static::storeInCache( $this, $this->{static::_fetchMainAttribute()} );

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

        public function delete() {
            static::deleteFromCache( $this->{static::_fetchMainAttribute} );
            parent::delete();
        }

    }
