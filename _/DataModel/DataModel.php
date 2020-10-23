<?php

    namespace Wrapped\_\DataModel;

    use \Serializable;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Mysql;
    use \Wrapped\_\Database\Facade\Query;
    use \Wrapped\_\Exception\Database\DatabaseException;
    use \Wrapped\_\Exception\Database\DatabaseObjectNotFound;
    use \Wrapped\_\Http\ParameterBag;
    use \Wrapped\_\ObjectAnalyser;
    use function \GuzzleHttp\json_decode;
    use function \GuzzleHttp\json_encode;

    abstract class DataModel
    implements Serializable {

        static protected $_analyserObjectCache = [];

        protected $_propertyHashes = [];

        protected static function _fetchPrimaryKey(): ?string {
            return "id";
        }

        /**
         * should only be used, to initialise a set of objects
         * this stores the hashvalues of the properties, to determine which columns
         * should be updated
         * [ "key" => "value" ]
         * @param type $data
         */
        public function initData( $data ) {

            $props          = static::fetchAnalyserObject()->fetchSetters();
            $lowerCaseProps = [];
            foreach ( $props as $key => &$value ) {
                $lowerCaseProps[strtolower( $key )] = $value;
            }

            foreach ( $data as $key => &$value ) {

                $key = strtolower( $key );

                if ( !isset( $lowerCaseProps[$key] ) ) {
                    continue;
                }

                if (
                    class_exists( $lowerCaseProps[$key]['type'] ) && class_implements( $lowerCaseProps[$key]['type'], PropertyObjectInterface::class )
                ) {
                    $class = $lowerCaseProps[$key]['type'];
                    $this->{$lowerCaseProps[$key]['setter']}( $class::hydrateFromString( $value ) );
                } else {
                    $this->{$lowerCaseProps[$key]['setter']}( $value );
                }
            }

            $this->_storePropertyStates();

            return $this;
        }

        /**
         * implementation for Serializable
         * @return string
         */
        public function serialize(): string {
            return $this->toJson();
        }

        public function toJson( $pretty = false ): string {
            return json_encode( $this->toArray(), $pretty ? 128 : null );
        }

        public function toArray(): array {

            $values = [];

            foreach ( $this::fetchAnalyserObject()->fetchColumnsWithGetters() as list("getter" => $getter, "column" => $column ) ) {

                $data = $this->{$getter}();

                $values[$column] = ( $data instanceof PropertyObjectInterface ) ? $data->dehydrateToString() : $data;
            }

            return $values;
        }

        public function fetchPrimaryKey() {
            return static::_fetchPrimaryKey();
        }

        public function fetchColumns() {
            return static::fetchAnalyserObject()->fetchAllColumns();
        }

        public function unserialize( $serialized ) {
            $this->initData( (array) json_decode( $serialized ) );
        }

        /**
         *
         * @return Mysql
         */
        public static function getDatabase(): DatabaseDriver {
            return in_array( DatabaseOverride::class, class_implements( static::class ) ) ? static::fetchDatabase() : Database::getConnection();
        }

        /**
         *
         * @return String
         */
        public static function getTableName(): string {
            return in_array( TablenameOverride::class, class_implements( static::class ) ) ? static::fetchTablename() : static::_getStaticClassName();
        }

        /**
         *
         * @return ?String
         */
        public static function getSchemaName(): ?string {
            return null;
        }

        public static function fetchBy( string $field, $value, DataModel $instance = null ) {
            return static::findSingle( [ $field => $value ] );
        }

        /**
         * creates an object with the given id as param
         * @param type $id
         * @return static
         * @throws DatabaseObjectNotFound
         */
        public static function get( $id ) {

            $pk = static::_fetchPrimaryKey();

            if ( $pk === null ) {
                throw new DatabaseException( "::get is not possible without PK" );
            }

            $result = static::fetchBy( $pk, $id );

            // for backwards compatibility
            if ( $result === null ) {
                throw new DatabaseObjectNotFound( "no such object found in database with name " . static::class . " and id {$id}" );
            }

            return $result;
        }

        public function reload() {
            return self::fetchBy( static::_fetchPrimaryKey(), $this->{static::_fetchPrimaryKey()} );
        }

        /**
         *
         * @param type $params an array like [ "id" => 1 ] or DbLogic instance
         * @return static[]
         */
        public static function find( $params, $orderBy = null, $order = "asc" ) {

            $query = new Query( static::getDatabase() );
            $query->select( ... static::fetchAnalyserObject()->fetchAllColumns() );
            $query->from( static::getSchemaName(), static::getTableName() );
            $query->where( $params );

            if ( $orderBy !== null ) {
                $query->order( [ [ $orderBy, $order ] ] );
            }

            return Collection::buildFromQuery( new (static::class), $query );
        }

        /**
         *
         * @param type $params
         * @param type $orderBy
         * @param type $order
         * @return static|null
         */
        public static function findSingle( $params = [], $orderBy = null, $order = "asc" ) {

            if ( $params instanceof DbLogic ) {
                throw new Exception( 'not supported' );
            }

            $query = new Query( static::getDatabase() );
            $query->select( ... static::fetchAnalyserObject()->fetchAllColumns() );
            $query->from( static::getSchemaName(), static::getTableName() );
            $query->where( $params );

            if ( $orderBy !== null ) {

                $query->order( [ [ $orderBy, $order ] ] );
            }

            $query->limit( 1 );

            return (new static() )->initData( $query->fetch() );
        }

        /**
         * fetches all entries from the database
         * @return static[]
         */
        public static function all( $orderBy = null, $order = "asc" ) {

            $query = new Query( static::getDatabase() );
            $query->select( ... static::fetchAnalyserObject()->fetchAllColumns() );
            $query->from( static::getSchemaName(), static::getTableName() );

            if ( $orderBy !== null ) {
                $query->order( [ [ $orderBy, $order ] ] );
            }

            return Collection::buildFromQuery( new (static::class), $query );
        }

        /**
         * returns last Record in DB ( according to the PK )
         * @return static
         */
        public static function last() {
            return static::findSingle( DbLogic::create()->order( static::_fetchPrimaryKey(), "desc" )->limit( 1 ) );
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

            if ( static::_fetchPrimaryKey() !== null ) {
                return $this->_isPropertyFuzzy( static::_fetchPrimaryKey(), $this->{static::_fetchPrimaryKey()} ) ? $this->insertIntoDatabase() : $this->saveToDatabase();
            } else {
                $this->insertIntoDatabase();
            }
        }

        private function insertIntoDatabase() {

            $insertData = [];

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( static::_fetchPrimaryKey() !== null && $col["column"] == static::_fetchPrimaryKey() && $this->{static::_fetchPrimaryKey()} === null ) {
                    continue;
                }

                $data = $this->{$col["getter"]}();

                if ( $data instanceof PropertyObjectInterface ) {
                    $data = $data->dehydrateToString();
                }

                $insertData[$col["column"]] = $data;
            }

            $query = new Query( static::getDatabase() );
            $query->insert(
                [ static::getSchemaName(), static::getTableName() ],
                array_keys( $insertData )
            );

            $query->values( $insertData );
            $query->returning( static::_fetchPrimaryKey() );
            $data = $query->fetch();

            // fetch autoincrement if defined
            if ( static::_fetchPrimaryKey() ) {
                $this->{static::_fetchPrimaryKey()} = $data[static::_fetchPrimaryKey()];
            }

            $this->_storePropertyStates();

            return $this;
        }

        private function saveToDatabase() {

            $pk = static::_fetchPrimaryKey();

            if ( $pk === null ) {
                throw new DatabaseException( "updating datamodels to database not possible without pk defined" );
            }

            $updateColumns = [];

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( $col["column"] === $pk ) {
                    continue;
                }

                $data = $this->{$col["getter"]}();

                if ( $this->_isPropertyFuzzy( $col["column"], $data ) ) {

                    if ( $data instanceof PropertyObjectInterface ) {
                        $data = $data->dehydrateToString();
                    }

                    $updateColumns[$col["column"]] = $data;
                }
            }

            if ( empty( $updateColumns ) ) {
                return $this;
            }

            $query = new Query( static::getDatabase() );
            $query->update(
                [ static::getSchemaName(), static::getTableName() ],
                $updateColumns
            );

            $query->where( [ static::_fetchPrimaryKey() => $this->{static::_fetchPrimaryKey()} ] );
            $query->run();

            return $this;
        }

        /**
         * used to determine which colums should be updated or not
         */
        protected function _storePropertyStates() {

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as &$col ) {

                $data = $this->{$col["getter"]}();

                if ( $data instanceof PropertyObjectInterface ) {
                    $data = $data->dehydrateToString();
                }

                $this->_propertyHashes[$col["column"]] = \crc32( $data );
            }
        }

        /**
         * checks whether
         * @param type $name
         * @param type $data
         * @return bool
         */
        protected function _isPropertyFuzzy( $name, $data ) {

            if ( $data instanceof PropertyObjectInterface ) {
                $data = $data->dehydrateToString();
            }

            return !\array_key_exists( $name, $this->_propertyHashes ) || $this->_propertyHashes[$name] !== \crc32( $data );
        }

        public function delete() {

            $pk = static::_fetchPrimaryKey();

            if ( $pk === null ) {
                throw new DatabaseException( 'deleting not possible without primary key' );
            }

            $pk = static::_fetchPrimaryKey();

            $query = new Query( static::getDatabase() );
            $query->delete( [ static::getSchemaName(), static::getTableName() ] );
            $query->where( [ static::_fetchPrimaryKey() => $this->{static::_fetchPrimaryKey()} ] );

            $query->run();

            return $this;
        }

        public static function count( string $what = "*", $params = null, $and = true ): int {

            $query = new Query( static::getDatabase() );
            $query->count( static::getSchemaName(), static::getTableName() );

            if ( $params ) {
                $query->where( $params );
            }

            return (int) $query->fetch()['count'];
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

    }
