<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use verfriemelt\wrapped\_\Database\Database;
use verfriemelt\wrapped\_\Database\DbLogic;
use verfriemelt\wrapped\_\Database\Driver\DatabaseDriver;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\PascalCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Relation\OneToManyRelation;
use verfriemelt\wrapped\_\DataModel\Attribute\Relation\OneToOneRelation;
use verfriemelt\wrapped\_\Exception\Database\DatabaseException;
use verfriemelt\wrapped\_\Exception\Database\DatabaseObjectNotFound;
use verfriemelt\wrapped\_\ParameterBag;

abstract class DataModel
{
    /** @var array<class-string,DataModelAnalyser> */
    protected static array $_analyserObjectCache = [];

    /** @var array<string,string|null> */
    protected array $_propertyHashes = [];

    final public function __construct()
    {
        $this->_storePropertyStates();
    }

    public static function getPrimaryKey(): string
    {
        return 'id';
    }

    /**
     * should only be used, to initialise a set of objects
     * this stores the hashvalues of the properties, to determine which columns
     * should be updated
     *
     * [ "key" => "value" ]
     *
     * @param array<string, mixed> $data
     */
    public function initData(array $data): static
    {
        $analyser = static::createDataModelAnalyser();

        foreach ($data as $key => $value) {
            $property = $analyser->fetchPropertyByName((string) $key);

            // property not found, we ignore this
            if (!$property) {
                continue;
            }

            if ($value === null && !$property->isNullable()) {
                continue;
            }

            $this->{$property->getSetter()}($this->hydrateProperty($property, $value));
        }

        $this->_storePropertyStates();

        return $this;
    }

    public static function fetchDatabase(): DatabaseDriver
    {
        return Database::getConnection();
    }

    public static function fetchTablename(): string
    {
        $name = static::createDataModelAnalyser()->getBaseName();
        $casing = (new PascalCase($name))->convertTo(static::createDataModelAnalyser()->fetchTableNamingConvention());

        return $casing->getString();
    }

    public static function fetchSchemaname(): ?string
    {
        return null;
    }

    protected function isPropertyInitialized(DataModelProperty $property): bool
    {
        return (new ReflectionProperty(static::class, $property->getName()))->isInitialized($this);
    }

    protected function hydrateProperty(DataModelProperty $property, mixed $input): mixed
    {
        $attributeType = $property->getType();

        // non typehinted property
        if ($attributeType === null) {
            return $input;
        }

        // preserve nulls
        if ($input === null) {
            return null;
        }

        // scalar properties
        if (in_array($attributeType, ['float', 'int', 'bool'])) {
            if (!settype($input, $attributeType)) {
                throw new Exception('casting of property failed');
            }

            return $input;
        }

        if (class_exists($attributeType) && in_array(
            PropertyObjectInterface::class,
            class_implements($attributeType),
        )) {
            return $attributeType::hydrateFromString($input);
        }

        return $input;
    }

    protected function dehydrateProperty(DataModelProperty $property): mixed
    {
        $propertyType = $property->getType();
        $value = $this->{$property->getGetter()}();

        if ($propertyType === null) {
            return $value;
        }

        $value = $this->{$property->getGetter()}();

        if ($value !== null && class_exists($propertyType) && $value instanceof PropertyObjectInterface) {
            return $value->dehydrateToString();
        }

        return $value;
    }

    public static function createDataModelAnalyser(): DataModelAnalyser
    {
        return static::$_analyserObjectCache[static::class] ??= new DataModelAnalyser(static::class);
    }

    public static function translateFieldName(string $fieldName): DataModelProperty
    {
        foreach (static::createDataModelAnalyser()->fetchProperties() as $field) {
            if ($fieldName === $field->fetchBackendName() || $fieldName === $field->getName()) {
                return $field;
            }
        }

        throw new Exception("field not translateable »{$fieldName}«");
    }

    public static function translateFieldNameArray(array $array, $torwardsDatabase = true): array
    {
        $keys = array_keys($array);

        if ($torwardsDatabase) {
            $keysTranslated = array_map(
                fn (string $field) => static::translateFieldName($field)->fetchBackendName(),
                $keys,
            );
        } else {
            $keysTranslated = array_map(fn (string $field) => static::translateFieldName($field)->getName(), $keys);
        }

        return array_combine($keysTranslated, array_values($array));
    }

    public function toJson($pretty = false): string
    {
        return \json_encode($this->toArray(), $pretty ? 128 : 0);
    }

    public function toArray(): array
    {
        $data = [];

        foreach (static::createDataModelAnalyser()->fetchProperties() as $attribute) {
            $data[$attribute->getName()] = $this->dehydrateProperty($attribute);
        }

        return $data;
    }

    /**
     * @return string[]
     */
    public function fetchColumns(): array
    {
        return array_map(
            fn (DataModelProperty $a) => $a->fetchBackendName(),
            static::createDataModelAnalyser()->fetchProperties(),
        );
    }

    public function fromJson(string $json): static
    {
        return $this->initData((array) \json_decode($json, null, 512, JSON_THROW_ON_ERROR));
    }

    public static function fetchBy(string $field, $value): ?static
    {
        return static::findSingle([$field => $value]);
    }

    /**
     * @return DataModelQueryBuilder<static>
     */
    protected static function buildQuery(): DataModelQueryBuilder
    {
        return new DataModelQueryBuilder(new static());
    }

    /**
     * @return DataModelQueryBuilder<static>
     */
    public static function buildSelectQuery(): DataModelQueryBuilder
    {
        $query = static::buildQuery();

        $query->select(...static::fetchSelectColumns());
        $query->from(static::fetchSchemaname(), static::fetchTablename());

        return $query;
    }

    /**
     * @return array<int,array{string,string}>
     */
    protected static function fetchSelectColumns(): array
    {
        return array_map(
            fn (DataModelProperty $a) => [static::fetchTablename(), $a->fetchBackendName()],
            static::createDataModelAnalyser()->fetchProperties(),
        );
    }

    /**
     * @return DataModelQueryBuilder<static>
     */
    public static function buildCountQuery(): DataModelQueryBuilder
    {
        $query = static::buildQuery();
        $query->count([static::fetchSchemaname(), static::fetchTablename()]);
        $query->disableAutomaticGroupBy();

        return $query;
    }

    public static function get(string|int $id): ?static
    {
        $pk = static::getPrimaryKey();

        if ($pk === null) {
            throw new DatabaseException('::get is not possible without PK');
        }

        return static::fetchBy($pk, $id) ?? throw new DatabaseObjectNotFound('no such object found in database with name ' . static::class . " and id {$id}");
    }

    public function reload(): static
    {
        $pk = static::getPrimaryKey();

        if ($pk === null) {
            throw new DatabaseException('::reload is not possible without PK');
        }

        $query = static::buildSelectQuery();
        $query->where([static::getPrimaryKey() => $this->{static::getPrimaryKey()}]);
        $query->limit(1);

        $data = $query->fetch();

        if ($data === null) {
            throw new DatabaseException('object was deleted');
        }

        $this->initData($data);

        return $this;
    }

    /**
     * @return Collection<static>
     */
    public static function find(array|DbLogic $params, ?string $orderBy = null, string $order = 'asc'): Collection
    {
        if ($params instanceof DbLogic) {
            $query = static::buildQueryFromDbLogic($params);
        } else {
            $query = static::buildSelectQuery();
            $query->where($params);

            if ($orderBy !== null) {
                $query->order([[$orderBy, $order]]);
            }
        }

        return Collection::buildFromQuery(new static(), $query);
    }

    public static function findSingle(
        array|DbLogic $params = [],
        ?string $orderBy = null,
        string $order = 'asc',
    ): ?static {
        if ($params instanceof DbLogic) {
            $query = static::buildQueryFromDbLogic($params);
        } else {
            $query = static::buildSelectQuery();

            if (!empty($params)) {
                $query->where($params);
            }

            if ($orderBy !== null) {
                $query->order([[$orderBy, $order]]);
            }
        }

        $query->limit(1);

        $result = $query->fetch();

        if ($result) {
            return (new static())->initData($result);
        }

        return null;
    }

    /**
     * @return DataModelQueryBuilder<static>
     */
    protected static function buildQueryFromDbLogic(DbLogic $logic): DataModelQueryBuilder
    {
        $query = static::buildSelectQuery();
        $query->translateDbLogic($logic);

        return $query;
    }

    /**
     * @return Collection<static>
     */
    public static function all($orderBy = null, $order = 'asc'): Collection
    {
        $query = static::buildSelectQuery();

        if ($orderBy !== null) {
            $query->order([[$orderBy, $order]]);
        }

        return Collection::buildFromQuery(new static(), $query);
    }

    public static function last(): ?static
    {
        return static::findSingle([], static::getPrimaryKey(), 'desc');
    }

    /**
     * @return Collection<static>
     */
    public static function take(int $count, int $offset = 0, array|DbLogic $params = []): Collection
    {
        $query = static::buildSelectQuery();

        if ($params instanceof DbLogic) {
            $query = static::buildQueryFromDbLogic($params);
        } else {
            $query = static::buildSelectQuery();
            $query->where($params);
        }

        $query->offset($offset);
        $query->limit($count);

        return Collection::buildFromQuery(new static(), $query);
    }

    public function save(): static
    {
        $pk = static::getPrimaryKey();

        if ($pk === null) {
            throw new DatabaseException('saving datamodels to database not possible without pk defined');
        }

        $pk = static::createDataModelAnalyser()->fetchPropertyByName(static::getPrimaryKey())->getName();

        if ($this->_isPropertyFuzzy($pk)) {
            $this->insertRecord();
        } else {
            $this->updateRecord();
        }

        $this->_storePropertyStates();

        return $this;
    }

    protected function prepareDataForStorage(bool $includeNonFuzzy = false): array
    {
        $result = [];

        foreach (static::createDataModelAnalyser()->fetchProperties() as $property) {
            // skip non initialized
            if (!$this->isPropertyInitialized($property)) {
                continue;
            }

            // skip pk
            if (static::getPrimaryKey() !== null && $property->getName() === static::getPrimaryKey(
            ) && $this->{static::getPrimaryKey()} === null) {
                continue;
            }

            if (!$includeNonFuzzy && !$this->_isPropertyFuzzy($property->getName())) {
                continue;
            }

            $result[$property->fetchBackendName()] = $this->dehydrateProperty($property);
        }

        return $result;
    }

    protected function insertRecord(): static
    {
        $insertData = $this->prepareDataForStorage(true);

        $query = static::buildQuery();
        $query->insert(
            [static::fetchSchemaname(), static::fetchTablename()],
            array_keys($insertData),
        );

        $query->values($insertData);
        $query->returning(static::getPrimaryKey());

        // store autoincrement
        $pk = static::createDataModelAnalyser()->fetchPropertyByName(static::getPrimaryKey());

        if ($pk === null) {
            throw new RuntimeException('pk not set');
        }

        $this->{$pk->getName()} = $this->hydrateProperty($pk, $query->fetch()[$pk->fetchBackendName()]);
        $this->_storePropertyStates();

        return $this;
    }

    protected function updateRecord(): static
    {
        $pk = static::getPrimaryKey();

        if ($pk === null) {
            throw new DatabaseException('updating datamodels to database not possible without pk defined');
        }

        $updateColumns = $this->prepareDataForStorage();

        if (empty(array_keys($updateColumns))) {
            return $this;
        }

        $query = static::buildQuery();
        $query->update(
            [static::fetchSchemaname(), static::fetchTablename()],
            $updateColumns,
        );

        $pk = (new Attribute\Naming\SnakeCase(static::getPrimaryKey()))->convertTo(
            new Attribute\Naming\CamelCase(),
        )->getString();

        $query->where([static::getPrimaryKey() => $this->{$pk}]);
        $query->run();

        return $this;
    }

    /**
     * used to determine which colums should be updated or not
     */
    protected function _storePropertyStates(): void
    {
        foreach (static::createDataModelAnalyser()->fetchProperties() as $attribute) {
            // not initialized
            if (!isset($this->{$attribute->getName()})) {
                $this->_propertyHashes[$attribute->getName()] = null;
                continue;
            }

            $this->_propertyHashes[$attribute->getName()] = \md5((string) $this->dehydrateProperty($attribute));
        }
    }

    protected function _isPropertyFuzzy(string $name): bool
    {
        $property = static::createDataModelAnalyser()->fetchPropertyByName($name);

        // not initialized
        if (!$this->isPropertyInitialized($property)) {
            return $this->_propertyHashes[$property->getName()] === null;
        }

        return $this->_propertyHashes[$name] !== \md5((string) $this->dehydrateProperty($property));
    }

    public function isDirty(): bool
    {
        foreach (static::createDataModelAnalyser()->fetchProperties() as $property) {
            if (!$this->isPropertyInitialized($property) || $this->_isPropertyFuzzy($property->getName())) {
                return true;
            }
        }

        return false;
    }

    public function delete(): static
    {
        $pk = static::getPrimaryKey();

        if ($pk === null) {
            throw new DatabaseException('deleting datamodels from database not possible without pk defined');
        }

        $query = static::buildQuery();
        $query->delete([static::fetchSchemaname(), static::fetchTablename()]);
        $query->where([static::getPrimaryKey() => $this->{static::getPrimaryKey()}]);
        $query->run();

        return $this;
    }

    public static function count(string $what = '*', DbLogic|array|null $params = null): int
    {
        $query = static::buildCountQuery();

        if ($params instanceof DbLogic) {
            $query->translateDbLogic($params);
        } elseif ($params !== null) {
            $query->where($params);
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
     */
    public static function createFromParameterBag(ParameterBag $params): static
    {
        return (new static())->initData($params->all());
    }

    public static function truncate(): void
    {
        static::fetchDatabase()->truncate(static::fetchTablename());
    }

    /**
     * returns short name of static
     */
    protected static function _getStaticClassName(): string
    {
        return static::createDataModelAnalyser()->getStaticName();
    }

    /**
     * @return static|Collection<static>|null
     */
    public function __call(string $propertyName, $args): DataModel|Collection|null
    {
        // creates reflecteion
        $reflection = new ReflectionClass($this);

        // checks the existance for the requested prop
        if (!\property_exists($this, $propertyName)) {
            throw new Exception("not existing property: {$propertyName}");
        }

        // fetches typ and prop informations
        $property = $reflection->getProperty($propertyName);
        $propertyType = $property->getType();

        $resolvAttribute = $property->getAttributes(OneToOneRelation::class)[0] ??
            $property->getAttributes(OneToManyRelation::class)[0] ?? null;

        if (!$resolvAttribute) {
            throw new Exception("missing relation attribute on {$propertyName}");
        }

        if ($this->{$propertyName} === null) {
            // fetches the data
            $resolv = $resolvAttribute->newInstance();

            if (new ($propertyType->getName()) instanceof Collection) {
                // query part
                $query = $args[0] ?? [];
                $query[] = [$resolv->rightColumn, '=', $this->{$resolv->leftColumn}];

                $model = $resolv->rightClass;
                $instance = new ($propertyType->getName())(...$model::find($query));
            } else {
                $instance = $propertyType->getName()::fetchBy($resolv->rightColumn, $this->{$resolv->leftColumn});
            }

            // set prop
            $this->{$propertyName} = $instance;
        }

        // return prop
        return $this->{$propertyName};
    }

    public static function fetchPredefinedJoins(string $class): ?callable
    {
        return null;
    }

    /**
     * @return DataModelQueryBuilder<static>
     */
    public static function with(DataModel $dest, ?callable $callback = null): DataModelQueryBuilder
    {
        $query = static::buildSelectQuery();
        $query->with($dest, $callback);

        return $query;
    }

    /**
     * this is somewhat of an lazy assumption
     */
    public function isPersisted(): bool
    {
        if (!\property_exists($this, static::getPrimaryKey())) {
            throw new RuntimeException('PK property does not exist');
        }

        /* @phpstan-ignore-next-line */
        return isset($this->{static::getPrimaryKey()});
    }
}
