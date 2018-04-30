<?php

    namespace Wrapped\_\Database\SQL;

    use \PDO;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\Driver\Driver;

    abstract class Command {

        protected $db;
        protected $table;
        protected $logic     = null;
        protected $fetchMode = PDO::FETCH_ASSOC;
        protected $bindings  = [];

        abstract function compile(): string;

        public function __construct( Driver $db ) {
            $this->db = $db;
        }

        public function table( string $table, string $alias = null ) {

            $alias = '';

            if ( $alias ) {
                $alias = " {$this->db->quoteIdentifier( $alias )}";
            }

            $this->table = $this->db->quoteIdentifier( $table ) . $alias;
            return $this;
        }

        public function setDbLogic( DbLogic $logic = null ): Command {
            $this->logic = $logic;
            return $this;
        }

        public function getDbLogic(): ?DbLogic {
            return $this->logic;
        }

        public function setFetchMode( $mode ): Command {
            $this->fetchMode = $mode;
            return $this;
        }

        public function getFetchMode(): int {
            return $this->fetchMode;
        }

        public function fetchBindings(): array {
            return $this->bindings;
        }

        public function hasBindings(): bool {
            return !empty( $this->bindings );
        }

        public function run() {
            return $this->db->run( $this );
        }

        protected function fetchLogic(): string {
            if ( $this->logic === null ) {
                return "";
            }

            return " {$this->logic->getString()}";
        }

    }
