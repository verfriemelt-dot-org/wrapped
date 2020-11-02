<?php

    namespace Wrapped\_\DataModel;

//    use \Wrapped\_\DataModel\Attribute\PropertyResolver;


    use \ReflectionClass;
    use \Serializable;
    use \Wrapped\_\Database\Database;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\DatabaseDriver;
    use \Wrapped\_\Database\Driver\Mysql;
    use \Wrapped\_\DataModel\Attribute\Naming\PascalCase;
    use \Wrapped\_\DataModel\Attribute\PropertyResolver;
    use \Wrapped\_\Exception\Database\DatabaseException;
    use \Wrapped\_\Exception\Database\DatabaseObjectNotFound;
    use \Wrapped\_\Http\ParameterBag;
    use function \json_decode;
    use function \json_encode;

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

            foreach ( static::createDataModelAnalyser()->fetchPropertyAttributes() as $attribute ) {

                $conventionName = $attribute->getNamingConvention()->getString();

                // skip attribute
                if ( !isset( $data[$conventionName] ) ) {
                    continue;
                }

                $this->{$attribute->getSetter()}( $this->hydrateAttribute( $attribute, $data[$conventionName] ) );
            }

            $this->_storePropertyStates();

            return $this;
        }

        private function hydrateAttribute( DataModelAttribute $attribute, $value ) {

            $attributeType = $attribute->getType();

            if ( class_exists( $attributeType ) && class_implements( $attributeType, PropertyObjectInterface::class ) ) {
                return $attributeType::hydrateFromString( $value );
            }

            return $value;
        }

        private function dehydrateAttribute( DataModelAttribute $attribute ) {

            $attributeType = $attribute->getType();

            $value = $this->{$attribute->getGetter()}();

            if ( $value !== null && class_exists( $attributeType ) && class_implements( $attributeType, PropertyObjectInterface::class ) ) {
                return $value->dehydrateToString();
            }

            return $value;
        }

        public static function createDataModelAnalyser(): DataModelAnalyser {

            if ( !isset( static::$_analyserObjectCache[static::class] ) ) {
                static::$_analyserObjectCache[static::class] = new DataModelAnalyser( new static );
            }

            return static::$_analyserObjectCache[static::class];
        }

        public static function translateFieldName( string $fieldName ): DataModelAttribute {

            foreach ( static::createDataModelAnalyser()->fetchPropertyAttributes() as $field ) {

                if ( $fieldName == $field->getNamingConvention()->getString() || $fieldName == $field->getName() ) {
                    return $field;
                }
            }

            throw new \Exception( "field not translateable »{$fieldName}«" );
        }

        public static function translateFieldNameArray( array $array, $torwardsDatabase = true ): array {

            $keys = array_keys( $array );

            if ( $torwardsDatabase ) {
                $keysTranslated = array_map( fn( string $field ) => static::translateFieldName( $field, true )->getNamingConvention()->getString(), $keys );
            } else {
                $keysTranslated = array_map( fn( string $field ) => static::translateFieldName( $field, false )->getName(), $keys );
            }

            return array_combine( $keysTranslated, array_values( $array ) );
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

            $data = [];

            foreach ( static::createDataModelAnalyser()->fetchPropertyAttributes() as $attribute ) {
                $data [$attribute->getName()] = $this->dehydrateAttribute( $attribute );
            }

            return $data;
        }

        public function fetchPrimaryKey() {
            return static::_fetchPrimaryKey();
        }

        public function fetchColumns() {
            return array_map( fn( DataModelAttribute $a ) => $a->getName(), static::createDataModelAnalyser()->fetchPropertyAttributes() );
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

            if ( in_array( TablenameOverride::class, class_implements( static::class ) ) ) {
                return static::fetchTablename();
            }

            $name   = static::createDataModelAnalyser()->getBaseName();
            $casing = (new PascalCase( $name ) )->convertTo( static::createDataModelAnalyser()->fetchTableNamingConvention() );

            return $casing->getString();
        }

        /**
         *
         * @return ?String
         */
        public static function getSchemaName(): ?string {
            return null;
        }

        public static function fetchBy( string $field, $value ) {
            return static::findSingle( [ $field => $value ] );
        }

        public static function buildSelectQuery(): DataModelQueryBuilder {

            $query = new DataModelQueryBuilder( new static );

            $query->select( ... array_map( fn( DataModelAttribute $a ) => [ static::getTableName(), $a->getNamingConvention()->getString() ], static::createDataModelAnalyser()->fetchPropertyAttributes() ) );
            $query->from( static::getSchemaName(), static::getTableName() );

            return $query;
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

            if ( $params instanceof DbLogic ) {
                $query = static::buildQueryFromDbLogic( $params );
            } else {

                $query = static::buildSelectQuery();
                $query->where( $params );

                if ( $orderBy !== null ) {
                    $query->order( [ [ $orderBy, $order ] ] );
                }
            }

            return Collection::buildFromQuery( new static, $query );
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
                $query = static::buildQueryFromDbLogic( $params );
            } else {

                $query = static::buildSelectQuery();

                if ( !empty( $params ) ) {
                    $query->where( $params );
                }


                if ( $orderBy !== null ) {
                    $query->order( [ [ $orderBy, $order ] ] );
                }
            }

            $query->limit( 1 );


            return (new static() )->initData( $query->fetch() );
        }

        private static function buildQueryFromDbLogic( DbLogic $logic ): DataModelQueryBuilder {

            $query = static::buildSelectQuery();
            $query->select( ... array_map( fn( DataModelAttribute $a ) => $a->getNamingConvention()->getString(), static::createDataModelAnalyser()->fetchPropertyAttributes() ) );


            $query->translateDbLogic( $logic );

            return $query;
        }

        /**
         * fetches all entries from the database
         * @return static[]
         */
        public static function all( $orderBy = null, $order = "asc" ) {

            $query = static::buildSelectQuery();
            $query->select( ... array_map( fn( DataModelAttribute $a ) => $a->getNamingConvention()->getString(), static::createDataModelAnalyser()->fetchPropertyAttributes() ) );


            if ( $orderBy !== null ) {
                $query->order( [ [ $orderBy, $order ] ] );
            }

            return Collection::buildFromQuery( new static, $query );
        }

        /**
         * returns last Record in DB ( according to the PK )
         * @return static
         */
        public static function last() {
            return static::findSingle( [], static::_fetchPrimaryKey(), 'desc' );
        }

        /**
         *
         * @param type $count
         * @param type $offset
         * @return static[]
         */
        public static function take( $count, $offset = null, $params = [] ) {

            $query = static::buildSelectQuery();

            if ( $params instanceof DbLogic ) {
                $query = static::buildQueryFromDbLogic( $params );
            } else {

                $query = static::buildSelectQuery();
                $query->where( $params );
            }

            $query->offset( $offset );
            $query->limit( $count );

            return Collection::buildFromQuery( new static, $query );
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

            foreach ( (new DataModelAnalyser( $this ) )->fetchPropertyAttributes() as $attribute ) {

                // skip pk
                if ( static::_fetchPrimaryKey() !== null && $attribute->getName() == static::_fetchPrimaryKey() && $this->{static::_fetchPrimaryKey()} === null ) {
                    continue;
                }

                $insertData[$attribute->getNamingConvention()->getString()] = $this->dehydrateAttribute( $attribute );
            }

            $insertData = static::translateFieldNameArray( $insertData );

            $query = static::buildSelectQuery();
            $query->insert(
                [ static::getSchemaName(), static::getTableName() ],
                array_keys( $insertData )
            );

            $query->values( $insertData );
            $query->returning( static::_fetchPrimaryKey() );

            $result = $query->fetch();

            // fetch autoincrement if defined
            if ( static::_fetchPrimaryKey() ) {
                $this->{static::_fetchPrimaryKey()} = $result[static::_fetchPrimaryKey()];
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

            foreach ( (new DataModelAnalyser( $this ) )->fetchPropertyAttributes() as $attribute ) {

                if ( $attribute->getName() === $pk ) {
                    continue;
                }

                $data = $this->{ $attribute->getGetter() }();

                if ( $this->_isPropertyFuzzy( $attribute->getName(), $data ) ) {

                    if ( $data instanceof PropertyObjectInterface ) {
                        $data = $data->dehydrateToString();
                    }

                    $updateColumns[$attribute->getNamingConvention()->getString()] = $data;
                }
            }

            if ( empty( $updateColumns ) ) {
                return $this;
            }

            $query = static::buildSelectQuery();
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

            foreach ( (new DataModelAnalyser( $this ) )->fetchPropertyAttributes() as $attribute ) {

                $data = $this->{$attribute->getGetter()}();

                if ( $data instanceof PropertyObjectInterface ) {
                    $data = $data->dehydrateToString();
                }

                $this->_propertyHashes[$attribute->getName()] = \crc32( $data );
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

            $query = static::buildSelectQuery();
            $query->delete( [ static::getSchemaName(), static::getTableName() ] );
            $query->where( [ static::_fetchPrimaryKey() => $this->{static::_fetchPrimaryKey()} ] );

            $query->run();

            return $this;
        }

        public static function count( string $what = "*", $params = null, $and = true ): int {

            $query = new DataModelQueryBuilder( new static );
            $query->count( [ static::getSchemaName(), static::getTableName() ] );

            if ( $params instanceof DbLogic ) {
                $query->translateDbLogic( $params );
            } elseif ( $params ) {
                $query->where( static::translateFieldNameArray( $params ) );
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
            return (new static() )->initData( $params->all() );
        }

        public static function truncate() {
            static::getDatabase()->truncate( static::getTableName() );
        }

        /**
         * returns short name of static
         * @return string
         */
        protected static function _getStaticClassName() {
            return static::createDataModelAnalyser()->getStaticName();
        }

        public function __call( string $propertyName, $args ): DataModel {

            // creates reflecteion
            $reflection = new ReflectionClass( $this );

            // checks the existance for the requested prop
            if ( !property_exists( $this, $propertyName ) ) {
                throw new \Exception( "not existing property: {$propertyName}" );
            }

            // fetches typ and prop informations
            $property     = $reflection->getProperty( $propertyName );
            $propertyType = $property->getType();

            $attachedAttributes = [];
            $resolvAttribute    = $property->getAttributes( PropertyResolver::class )[0] ?? null;

            if ( !$resolvAttribute ) {
                throw new \Exception( "missing resolvAttribute on {$propertyName}" );
            }

            if ( $this->{ $propertyName } === null ) {

                // fetches the data
                $resolv                  = $resolvAttribute->newInstance();
                // set prop
                $this->{ $propertyName } = $propertyType->getName()::fetchBy( $resolv->destinationProperty, $this->{ $resolv->sourceProperty } );
            }

            // return prop
            return $this->{ $propertyName };
        }

        public static function fetchPredefinedJoins( string $class ): ?callable {
            return null;
        }

        public static function with( DataModel $dest, callable $callback ): DataModelQueryBuilder {

            $query = static::buildSelectQuery();
            $query->with( $dest, $callback );

            return $query;
        }

    }
