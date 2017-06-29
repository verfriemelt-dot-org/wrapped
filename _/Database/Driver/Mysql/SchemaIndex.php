<?php

    namespace Wrapped\_\Database\Driver\Mysql;

    class SchemaIndex {

        private
            $name,
            $schema,
            $type     = self::INDEX,
            $dropflag = false,
            $columns  = [];

        const
            PRIMARY = 0,
            UNIQUE = 1,
            INDEX = 2,
            SPATIAL = 3;

        public function __construct( $name, Schema $schema ) {
            $this->name   = $name;
            $this->schema = $schema;
        }

        public function drop() {
            $this->dropflag = true;
            return $this;
        }

        public function addColumn( SchemaColumn $column ) {
            $this->columns[] = $column;
            return $this;
        }

        public function primary() {
            $this->type = static::PRIMARY;
            return $this;
        }

        public function index() {
            $this->type = static::INDEX;
            return $this;
        }

        public function unique() {
            $this->type = static::UNIQUE;
            return $this;
        }

        public function stringify() {

            $sql = "ALTER TABLE `{$this->schema->getTableName()}` ";

            if ( $this->dropflag ) {
                return $sql . " DROP INDEX `{$this->name}`";
            }


            switch ( $this->type ) {
                case self::PRIMARY:
                    $sql .= "ADD PRIMARY KEY ";
                    break;
                case self::UNIQUE:
                    $sql .= "ADD UNIQUE KEY ";
                    break;
                case self::INDEX:
                    $sql .= "ADD KEY ";
                    break;
                case self::SPATIAL:
                    $sql .= "ADD SPATIAL KEY ";
                    break;
            }

            if ( $this->type !== self::PRIMARY ) {
                $sql .= "`{$this->name}` ";
            }

            $sql .= "(";

            $colCount = count( $this->columns );
            for ( $i = 0; $i < $colCount; $i++ ) {

                $sql .= "`{$this->columns[$i]->getName()}`";

                if ( $i + 1 < $colCount ) {
                    $sql .= ",";
                }
            }

            $sql .= ")";
            return $sql;
        }

    }
