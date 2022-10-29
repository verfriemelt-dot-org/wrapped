<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\CacheHelper;

    use verfriemelt\wrapped\_\Cache\CacheFactory;

    trait DataModelCaching
    {
        protected static function storeInCache($instance, string|int $key): bool
        {
            if (!CacheFactory::hasCache()) {
                return false;
            }

            $cache = CacheFactory::getCache();
            return $cache->set(static::class . (string) $key, serialize($instance), 600);
        }

        protected static function retriveFromCache($key)
        {
            if (!CacheFactory::hasCache()) {
                return false;
            }

            if ($data = CacheFactory::getCache()->get(static::class . $key)) {
                return unserialize($data);
            }

            return false;
        }

        protected static function deleteFromCache(string $key)
        {
            if (!CacheFactory::hasCache()) {
                return false;
            }

            $cache = CacheFactory::getCache();
            $cache->delete(static::class . $key);
        }

        public function save(): static
        {
            $result = parent::save();
            static::storeInCache($this, $this->{static::getPrimaryKey()});

            return $result;
        }

        public static function get(string|int $id): ?static
        {
            $instance = static::retriveFromCache($id);

            if (!$instance) {
                $instance = parent::get($id);
                static::storeInCache($instance, $id);
            }

            return $instance;
        }

        /**
         * this should only be used on unique column
         *
         * @param type $value
         *
         * @return type
         */
        public static function fetchBy(string $field, $value): ?static
        {
            // mapping
            $pk = static::retriveFromCache($field . (string) $value);

            if ($pk === false) {
                $instance = parent::fetchBy($field, $value);

                // return null if not found
                if (!$instance) {
                    return null;
                }

                // store instance itself
                static::storeInCache($instance, $instance->{static::getPrimaryKey()});

                // fetch keyvalue pair for set instance.
                static::storeInCache($instance->{static::getPrimaryKey()}, $field . (string) $value);

                return $instance;
            }

            return static::get($pk);
        }

        public function delete(): static
        {
            static::deleteFromCache($this->{static::getPrimaryKey()});
            parent::delete();
        }
    }
