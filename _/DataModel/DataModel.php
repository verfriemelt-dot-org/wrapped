<?php

    namespace Wrapped\_\DataModel;

    use \Serializable;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Mysql;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Clause\Limit;
    use \Wrapped\_\Database\SQL\Clause\Order;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Insert;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Command\Values;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;
    use \Wrapped\_\DataModel\Collection\Collection;
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

            $db = static::getDatabase();

            $select = new Select();
            $from   = new From( new Identifier( static::getSchemaName(), static::getTableName() ) );
            $limit  = new Limit( new Value( 1 ) );

            foreach ( static::fetchAnalyserObject()->fetchAllColumns() as $col ) {
                $select->add( new Identifier( $col ) );
            }

            $where = new Where(
                ( new Expression() )
                    ->add( new Identifier( $field ) )
                    ->add( new Operator( '=' ) )
                    ->add( new Value( $value ) )
            );

            $res = $db->run(
                (new Statement( $select ) )
                    ->add( $from )
                    ->add( $where )
                    ->add( $limit )
            );

            if ( $res->rowCount() === 0 ) {
                return null;
            }

            if ( $instance ) {
                return $instance->initData( $res->fetch() );
            }

            return (new static() )->initData( $res->fetch() );
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

            if ( $params instanceof DbLogic ) {

                throw new Exception( 'not supported' );
            }

            $db = static::getDatabase();

            $select = new Select();
            $from   = new From( new Identifier( static::getSchemaName(), static::getTableName() ) );
            $limit  = new Limit( new Value( 1 ) );

            foreach ( static::fetchAnalyserObject()->fetchAllColumns() as $col ) {
                $select->add( new Identifier( $col ) );
            }

            $whereExpression = new \Wrapped\_\Database\SQL\Expression\Expression();

            $counter = 0;
            foreach ( $params as $field => $value ) {

                $whereExpression
                    ->add( new Identifier( $field ) )
                    ->add( new Operator( '=' ) )
                    ->add( new Value( $value ) );

                if ( ++$counter < count( $params ) ) {
                    $whereExpression->add( new Operator( 'and' ) );
                }
            }

            $where = new Where( $whereExpression );

            $stmt = (new Statement( $select ) )
                ->add( $from )
                ->add( $where )
            ;

            if ( $orderBy !== null ) {
                $stmt->add(
                    (new Order( ) )
                        ->add( new Identifier( $orderBy ), $order )
                );
            }

            $stmt->add( $limit );

            $res = $db->run( $stmt );

            if ( $res->rowCount() === 0 ) {
                return null;
            }

            return (new static() )->initData( $res->fetch() );
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
                return $this->_isPropertyFuzzy( static::_fetchPrimaryKey(), $this->{static::_fetchPrimaryKey()} ) ? $this->_insertDbRecord() : $this->_updateDbRecord();
            } else {
                $this->_insertDbRecord();
            }
        }

        private function _insertDbRecord() {

            $db = static::getDatabase();

            $insert = new Insert( new Identifier( static::getTableName() ) );
            $values = new Values();

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( static::_fetchPrimaryKey() !== null && $col["column"] == static::_fetchPrimaryKey() && $this->{static::_fetchPrimaryKey()} === null ) {
                    continue;
                }

                $data = $this->{$col["getter"]}();

                if ( $data instanceof PropertyObjectInterface ) {
                    $data = $data->dehydrateToString();
                }

                $insert->add( new Identifier( $col["column"] ) );
                $values->add( new Value( $data ) );
            }

            $statement = new Statement( $insert );
            $statement->add( $values );

            $db->run( $statement );

            // should be refactored
            if ( static::_fetchPrimaryKey() == "id" ) {
                $id = $db->fetchConnectionHandle()->lastInsertId();
                $this->setId( $id );
            }

            $this->_storePropertyStates();

            return $this;
        }

        public function _updateDbRecord() {

            $pk = static::_fetchPrimaryKey();

            if ( $pk === null ) {
                throw new DatabaseException( "updating datamodels to database not possible without pk defined" );
            }

            $logic = (new DbLogic() )->where( $pk, "=", $this->{$pk} );

            $db     = static::getDatabase();
            $update = $db->update( static::getTableName(), static::getSchemaName() );
            $update->setDbLogic( $logic );

            $hasUpdates = false;

            foreach ( static::fetchAnalyserObject()->fetchColumnsWithGetters() as $col ) {

                if ( $col["column"] === $pk ) {
                    continue;
                }

                $currentData = $this->{$col["getter"]}();

                if ( $this->_isPropertyFuzzy( $col["column"], $currentData ) ) {

                    if ( $currentData instanceof PropertyObjectInterface ) {
                        $currentData = $currentData->dehydrateToString();
                    }

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

        /**
         * deletes object
         * @return bool
         */
        public function delete() {

            $pk = static::_fetchPrimaryKey();

            if ( $pk === null ) {
                throw new DatabaseException( 'deleting not possible without primary key' );
            }

            if ( $this->{$pk} === null ) {
                return;
            }

            $db    = static::getDatabase();
            $logic = (new DbLogic() )->where( $pk, "=", $this->{$pk} );

            $delete = $db->delete( static::getTableName(), static::getSchemaName() );
            $delete->setDbLogic( $logic );
            $delete->run();
        }

        public static function count( string $what = "*", $by = null, $and = true ): int {

            $db     = static::getDatabase();
            $select = $db->select( static::getTableName(), static::getSchemaName() );

            if ( is_array( $by ) ) {

                $count = count( $by );
                $logic = new DbLogic();
                $i     = 0;

                foreach ( $by as $column => &$value ) {

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

    }
