<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Database\Driver;

use PDO;
use PDOException;
use PDOStatement;
use Symfony\Component\String\Exception\RuntimeException;
use verfriemelt\wrapped\_\Database\DbLogic;
use verfriemelt\wrapped\_\Database\SQL\QueryPart;
use verfriemelt\wrapped\_\Exception\Database\DatabaseException;

abstract class DatabaseDriver
{
    public string $connectionName;

    protected string $currentDatabase;

    protected string $currentUsername;

    /** @var PDOStatement[] */
    protected array $statements = [];

    protected PDOStatement $lastStatement;

    /** @var array<string,string|int> */
    protected array $config = [];

    public static bool $debug = false;

    public static array $debugHistory = [];

    public static string $debugLastStatement;

    public static array $debugLastParams = [];

    public static int $debugQuerieCount = 0;

    public static int $counter = 0;

    public static float $time = 0;

    private int $nestedTransactionCounter = 0;

    protected const PDO_NAME = 'undefined';

    public PDO $connectionHandle;

    protected string $connectionString;

    abstract public function quoteIdentifier(string $ident): string;

    public function __construct(string $name, string $user, string $password, string $host, string $database, int $port = null)
    {
        $this->connectionName = $name;

        $this->config['dbUsername'] = $user;
        $this->config['dbPassword'] = $password;
        $this->config['dbPassword'] = $password;
        $this->config['dbDatabase'] = $database;
        $this->config['dbHost'] = $host;
        $this->config['dbPort'] = $port;
    }

    public function fetchConnectionHandle(): PDO
    {
        return $this->connectionHandle;
    }

    protected function getConnectionString(): string
    {
        $this->connectionString = static::PDO_NAME . ":host={$this->config['dbHost']};";

        if ($this->config['dbPort'] !== null) {
            $this->connectionString .= "port={$this->config['dbPort']};";
        }

        $this->connectionString .= "dbname={$this->config['dbDatabase']}";

        return $this->connectionString;
    }

    public function fetchConnectionString(): string
    {
        return $this->connectionString;
    }

    public function disconnet()
    {
        $this->connectionHandle = null;
    }

    public function connect(): void
    {
        try {
            $this->connectionHandle = new PDO(
                $this->getConnectionString(),
                $this->config['dbUsername'],
                $this->config['dbPassword']
            );

            // switch to error mode to exceptions
            $this->connectionHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $msg = $e->getMessage();

            if ($msg === 'could not find driver') {
                throw new DatabaseException('PDO Mysql Driver not available');
            }

            throw new DatabaseException("PDO Exception {$e->getMessage()}");
        }

        // unset config data
        $this->currentDatabase = $this->config['dbDatabase'];
        $this->currentUsername = $this->config['dbUsername'];
        $this->config = [];
    }

    public function bind(PDOStatement $statement, $param, $var): static
    {
        $type = PDO::PARAM_STR;

        switch (gettype($var)) {
            case 'boolean':
                $type = PDO::PARAM_BOOL;
                break;
            case 'integer':
                $type = PDO::PARAM_INT;
                break;
            case 'NULL':
                $type = PDO::PARAM_NULL;
                break;
        }

        $statement->bindValue($param, $var, $type);

        return $this;
    }

    protected function bindLast($param, $var): static
    {
        self::$debugLastParams['param'][] = $param;
        self::$debugLastParams['var'][] = $var;

        $this->bind($this->lastStatement, $param, $var);
        return $this;
    }

    public function execute(PDOStatement $statement): true
    {
        $start = microtime(true);

        try {
            $statement->execute();
        } catch (PDOException $e) {
            $message = $e->getMessage() . "\n\n" . self::$debugLastStatement . "\n\n" . print_r(
                self::$debugLastParams,
                true
            );
            throw (new DatabaseException($message))->setSqlState($e->getCode());
        }

        $time = microtime(true) - $start;

        if (static::$debug) {
            static::$debugHistory[] = [
                'con' => $this->connectionName,
                'count' => ++static::$debugQuerieCount,
                'time' => $time,
                'statement' => self::$debugLastStatement,
                'data' => self::$debugLastParams,
                //                    "stack" => $log
            ];
        }

        static::$time += $time;
        return true;
    }

    public function getLastResult(): PDOStatement
    {
        return $this->lastStatement;
    }

    public function executeLast(): bool
    {
        return $this->execute($this->lastStatement);
    }

    public function prepare(string $statement): static
    {
        self::$debugLastParams = [];
        self::$debugLastStatement = $statement;

        $this->lastStatement = $this->connectionHandle->prepare($statement) ?: throw new \RuntimeException(
            'preparing the statement failed'
        );

        return $this;
    }

    public function run(QueryPart $query): PDOStatement
    {
        $this->prepare($query->stringify($this));

        foreach ($query->fetchBindings() as $bind => $value) {
            $this->bindLast($bind, $value);
        }

        $this->executeLast();

        $result = $this->lastStatement;
        $result->setFetchMode(PDO::FETCH_ASSOC);

        return $result;
    }

    public function quote(string $data): string
    {
        return $this->connectionHandle->quote($data) ?: throw new \RuntimeException('quoting failed');
    }

    public function truncate(string $tableName): int
    {
        $statement = "TRUNCATE {$tableName} RESTART IDENTITY CASCADE";
        $this->prepare($statement);
        $this->executeLast();

        return $this->lastStatement->rowCount();
    }

    public function getCurrentDatabase(): string
    {
        return $this->currentDatabase;
    }

    public function getCurrentUsername(): string
    {
        return $this->currentUsername;
    }

    public function startTransaction(): bool
    {
        if ($this->nestedTransactionCounter === 0) {
            ++$this->nestedTransactionCounter;
            $this->connectionHandle->beginTransaction() ?: throw new RuntimeException('transaction failed');
        } else {
            $this->query("SAVEPOINT wrapped{$this->nestedTransactionCounter}");
            ++$this->nestedTransactionCounter;
        }

        return true;
    }

    public function inTransaction(): bool
    {
        return $this->connectionHandle->inTransaction();
    }

    public function rollbackTransaction(): bool
    {
        if ($this->nestedTransactionCounter === 1) {
            --$this->nestedTransactionCounter;
            return $this->connectionHandle->rollBack();
        } elseif ($this->nestedTransactionCounter > 1) {
            --$this->nestedTransactionCounter;
            $this->query("ROLLBACK TO wrapped{$this->nestedTransactionCounter}");
            return true;
        }

        throw new \RuntimeException('no active transaction to rolback');
    }

    public function commitTransaction(): bool
    {
        if ($this->nestedTransactionCounter === 0) {
            throw new \RuntimeException('no active transaction to commmit');
        }

        if ($this->nestedTransactionCounter > 1) {
            --$this->nestedTransactionCounter;
            return true;
        }

        $this->nestedTransactionCounter = 0;
        return $this->connectionHandle->commit();
    }

    public function query(string $sql): PDOStatement
    {
        $this->prepare($sql);
        $this->executeLast();
        return $this->lastStatement;
    }

    public function queryWithDbLogic(string $sql, DbLogic $dbLogic, bool $precompiled = false): PDOStatement
    {
        // uh is this hacky
        if (!$precompiled) {
            $this->prepare($sql . $dbLogic->compile($this));
        } else {
            $this->prepare($sql);
        }

        foreach ($dbLogic->fetchBindings() as $bind => $value) {
            $this->bindLast($bind, $value);
        }

        $this->executeLast();

        $result = $this->lastStatement;
        $result->setFetchMode(PDO::FETCH_ASSOC);

        return $result;
    }

    public function setAttribute(int $key, mixed $value): static
    {
        $this->connectionHandle->setAttribute($key, $value);
        return $this;
    }
}
