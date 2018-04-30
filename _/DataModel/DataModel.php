<?php

    namespace Wrapped\_\DataModel;

    use \Serializable;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Mysql;
    use \Wrapped\_\DataModel\Collection\Collection;
    use \Wrapped\_\Exception\Database\DatabaseObjectNotFound;
    use \Wrapped\_\Http\ParameterBag;
    use \Wrapped\_\ObjectAnalyser;

    abstract class DataModel
    implements Serializable {

        static public $_analyserObjectCache = [];
        protected $_propertyHashes          = [];

        /**
         * should only be used, to initialise a set of objects
         * this stores the hashvalues of the properties, to determine which columns
         * should be updated
         * [ "key" => "value" ]
         * @param type $data
         */
        public function initData( $data ) {

            foreach ( $data as $key => $value ) {

                $method = "set" . ucfirst( $key );

                if ( \method_exists( $this, $method ) ) {
                    $this->$method( $value );
                }
            }

            $this->_storePropertyStates();

            return $this;
        }

        public function serialize() {
            return $this->toJson();
        }

        public function toJson( $pretty = false ): string {

            $analyser = $this::fetchAnalyserObject();
            $values   = [];

            foreach ( $analyser->fetchColumnsWithGetters() as list("getter" => $getter, "column" => $column ) ) {
                $values[$column] = $this->{$getter}();
            }

            return json_encode( $values, $pretty ? 128 : null );
        }

        public function fetchColumns() {
            $analyser = new ObjectAnalyser( static::class );
            return $analyser->fetchAllColumns();
        }

        public function unserialize( $serialized ) {
            $this->initData( (array) json_decode( $serialized ) );
        }

        protected static function _fetchMainAttribute(): string {
            return "id";
        }

        /**
         *
         * @return Mysql
         */
        public static function getDatabase() {
            return in_array( DatabaseOverride::class, class_implements( static::class ) ) ? static::fetchDatabase() : Database::getConnection();
        }

        /**
         *
         * @return String
         */
        public static function getTableName() {
            return in_array( TablenameOverride::class, class_implements( static::class ) ) ? static::fetchTablename() : static::_getStaticClassName();
        }

        /**
         * creates an object with the given id as param
         * @param type $id
         * @return static
         * @throws DatabaseObjectNotFound
         */
        public static function get( $id ) {

            $tableName = static::getTableName();
            $db        = static::getDatabase();

            $select = $db->select( $tableName );
            $select->all();
            $select->setDbLogic( (new DbLogic() )->where( static::_fetchMainAttribute(), "=", $id )->limit( 1 ) );

            $res = $select->run();

            if ( $res->rowCount() === 0 ) {
                throw new DatabaseObjectNotFound( "no such object found in database with name " . static::class . " and id {$id}" );
            }

            return (new static() )->initData( $res->fetch() );
        }

        public function reload() {

            $tableName = static::getTableName();
            $db        = static::getDatabase();

            $select = $db->select( $tableName );
            $select->all();
            $select->setDbLogic( (new DbLogic() )->where( static::_fetchMainAttribute(), "=", $this->{static::_fetchMainAttribute()} )->limit( 1 ) );

            $res = $select->run();

            if ( $res->rowCount() === 0 ) {
                throw new DatabaseObjectNotFound( "no such object found in database with name " . static::class . " and id {$id}" );
            }

            $this->initData( $res->fetch() );

            return $this;
        }

        /**
         *
         * @param type $params an array like [ "id" => 1 ] or DbLogic instance
         * @return static[]
         */
        public static function find( $params, $orderBy = null, $order = "asc" ) {

            $c  = new Collection();
            $co = $c->from( static::class );

            if ( $params instanceof DbLogic ) {
                $co->setLogic( $params );
            } else {
                $co->createLogic()->parseArray( $params );
            }

            if ( $orderBy !== null ) {
                $co->getDbLogic()->order( $orderBy, $order );
            }

            return $c->get();
        }

        /**
         *
         * @param type $params
         * @param type $orderBy
         * @param type $order
         * @return static|null
         */
        public static function findSingle( $params = [], $orderBy = null, $order = "asc" ) {

            $c  = new Collection();
            $co = $c->from( static::class );

            if ( $params instanceof DbLogic ) {
                $co->setLogic( $params );
            } else {
                $co->createLogic()->parseArray( $params );
            }

            if ( $orderBy !== null ) {
                $co->getDbLogic()->order( $orderBy, $order );
            }

            $co->getDbLogic()->limit( 1 );
            $collectionResult = $c->get();

            return $collectionResult->isEmpty() ? null : $collectionResult->current();
        }

        /**
         * fetches all entries from the database
         * @return static[]
         */
        public static function all( $orderBy = null, $order = "asc" ) {

            $c  = new Collection();
            $co = $c->from( static::class );

            if ( $orderBy !== null ) {
                $co->createLogic();
                $co->getDbLogic()->order( $orderBy, $order );
            }

            return $c->get();
        }

        /**
         *
         * @param type $count
         * @param type $offset
         * @return static[]
         */
        public static function take( $count, $offset = null, $params = [] ) {

            $c  = new Collection();
            $co = $c->from( static::class );

            if ( $params instanceof DbLogic ) {
                $co->setLogic( $params );
            } else {
                $co->createLogic()->parseArray( $params );
            }

            $co->getDbLogic()->offset( $offset );
            $co->getDbLogic()->limit( $count );

            return $c->get();
        }

        /**
         *
         * @return static
         */
        public function save() {
            return $this->_isPropertyFuzzy( static::_fetchMainAttribute(), $this->{static::_fetchMainAttribute()} ) ? $this->_insertDbRecord() : $this->_updateDbRecord();
        }

        private function _insertDbRecord() {

            $db     = static::getDatabase();
            $insert = $db->insert( static::getTableName() );

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( $col["column"] == static::_fetchMainAttribute() && $this->{static::_fetchMainAttribute()} === null ) {
                    continue;
                }

                $insert->insert( $col["column"], $this->{$col["getter"]}() );
            }

            $insert->run();
            $id = $db->fetchConnectionHandle()->lastInsertId();

            if ( static::_fetchMainAttribute() == "id" ) {
                $this->setId( $id );
            }

            $this->_storePropertyStates();

            return $this;
        }

        public function _updateDbRecord() {

            $logic = (new DbLogic() )->where( static::_fetchMainAttribute(), "=", $this->{static::_fetchMainAttribute()} );

            $db     = static::getDatabase();
            $update = $db->update( static::getTableName() );
            $update->setDbLogic( $logic );

            $hasUpdates = false;

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( $col["column"] === static::_fetchMainAttribute() ) {
                    continue;
                }

                $currentData = $this->{$col["getter"]}();

                if ( $this->_isPropertyFuzzy( $col["column"], $currentData ) ) {
                    $update->update( $col["column"], $currentData );

                    $hasUpdates = true;
                }
            }

            if ( $hasUpdates ) {
                $update->run();
            }

            return $this;
        }

        /**
         * used to determine which colums should be updated or not
         */
        protected function _storePropertyStates() {

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {
                $this->_propertyHashes[$col["column"]] = \crc32( $this->{$col["getter"]}() );
            }
        }

        /**
         * checks whether
         * @param type $name
         * @param type $value
         * @return bool
         */
        protected function _isPropertyFuzzy( $name, $value ) {
            return !\array_key_exists( $name, $this->_propertyHashes ) || $this->_propertyHashes[$name] !== \crc32( $value );
        }

        /**
         * deletes object
         * @return bool
         */
        public function delete() {

            if ( $this->{static::_fetchMainAttribute()} === null ) {
                return;
            }

            $db    = static::getDatabase();
            $logic = (new DbLogic() )->where( static::_fetchMainAttribute(), "=", $this->{static::_fetchMainAttribute()} );

            $delete = $db->delete( static::getTableName() );
            $delete->setDbLogic( $logic );
            $delete->run();
        }

        public static function count( string $what = "*", $by = null, $and = true ): int {

            $db     = static::getDatabase();
            $select = $db->select( static::getTableName() );

            if ( is_array( $by ) ) {

                $count = count( $by );
                $logic = new DbLogic();
                $i     = 0;

                foreach ( $by as $column => $value ) {

                    if ( is_array( $value ) ) {
                        $logic->where( $column, "IN", $value );
                    } else {
                        $logic->where( $column, "=", $value );
                    }

                    if ( ++$i + 1 <= $count ) {
                        ($and) ? $logic->addAnd() : $logic->addOr();
                    }
                }

                $select->setDbLogic( $logic );
            }

            if ( $by instanceof DbLogic ) {
                $select->setDbLogic( $by );
            }

            $res = $select->count( $what, 'count' )->run();

            return (int) $res->fetch()["count"];
        }

        /**
         * creates and object instance and mapps the attributes on the object
         * accordingly by the names in the bag.
         * if there is extra data, which is not present on the dataobject, that
         * data is simply ignored.
         *
         * this method uses the setters of that object
         *
         * @param ParameterBag $params
         * @return \static returns the unsaved object instance
         */
        public static function createFromParameterBag( ParameterBag $params ) {

            $instance = new static();
            $setters  = static::fetchAnalyserObject()->fetchSetters();

            foreach ( $setters as $setter ) {

                if ( !$params->has( $setter["column"] ) ) {
                    continue;
                }

                $instance->{$setter["setter"]}( $params->get( $setter["column"] ) );
            }

            return $instance;
        }

        /**
         *
         * @return ObjectAnalyser;
         */
        static public function fetchAnalyserObject() {

            if ( !isset( static::$_analyserObjectCache[static::class] ) ) {
                static::$_analyserObjectCache[static::class] = new ObjectAnalyser( static::class );
            }

            return static::$_analyserObjectCache[static::class];
        }

        public static function truncate() {
            static::getDatabase()->truncate( static::getTableName() );
        }

        /**
         * returns short name of static
         * @return string
         */
        protected function _getClassName() {
            return static::fetchAnalyserObject()->getObjectShortName();
        }

        /**
         * returns short name of static
         * @return string
         */
        protected static function _getStaticClassName() {
            return static::fetchAnalyserObject()->getObjectShortName();
        }

        public function __clone() {

            $mainAttribute = static::_fetchMainAttribute();
            $setter        = "set" . ucfirst( $mainAttribute );

            $this->{$setter}( null );
        }

    }
