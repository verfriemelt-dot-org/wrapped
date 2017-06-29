<?php

    namespace Wrapped\_\Database\Driver\Mysql;

    class SchemaColumn {

        private $name;
        private $primary   = false;
        private $increment = false;
        private $default   = false;
        private $null      = false;
        private $length    = false;
        private $drop      = false;
        private $unsigned  = false;
        private $enumOptions;
        private $oldName;

        public function __construct( $name ) {
            $this->name    = $name;
            $this->oldName = $name;
        }

        /**
         *
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function drop() {
            $this->drop = true;
            return $this;
        }

        /**
         *
         * @return bool
         */
        public function dropped() {
            return $this->drop;
        }

        public function unsigned() {
            $this->unsigned = true;
            return $this;
        }

        /**
         *
         * @param type $name
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function renameFrom( $name ) {
            $this->oldName = $name;
            return $this;
        }

        /**
         *
         * @return string
         */
        public function getOldName() {
            return $this->oldName;
        }

        /**
         *
         * @return string
         */
        public function getName() {
            return $this->name;
        }

        /**
         *
         * @return bool
         */
        public function isPrimary() {
            return $this->primary;
        }

        /**
         *
         * @param type $bool
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function primary( $bool = true ) {
            $this->primary = $bool;
            return $this;
        }

        /**
         *
         * @param type $type
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function type( $type = "varchar" ) {
            $this->type = $type;
            return $this;
        }

        /**
         *
         * @param type $length
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function length( $length ) {
            $this->length = $length;
            return $this;
        }

        /**
         *
         * @param type $bool
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function nullable( $bool = true ) {
            $this->null = $bool;
            return $this;
        }

        /**
         *
         * @param type $default
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function defaultValue( $default ) {
            $this->default = $default;
            return $this;
        }

        /**
         *
         * @param type $increment
         * @return \Wrapped\_\Database\Driver\Mysql\SchemaColumn
         */
        public function increment( $increment = true ) {
            $this->increment = $increment;
            return $this;
        }

        public function enumOptions( ... $options ) {
            $this->enumOptions = $options;
            return $this;
        }

        /**
         * raw sql query part defining that column
         * @return string
         */
        public function stringify() {

            $syntax = "`{$this->name}` {$this->type}";
            $syntax .= $this->length !== false ? "({$this->length})" : " ";

            if ( $this->enumOptions ) {
                $syntax .= "('" . implode("','",$this->enumOptions) . "')";
            }

            $syntax .= $this->unsigned ? " UNSIGNED " : "";
            $syntax .= $this->null ? " NULL " : " NOT NULL ";

            if ( $this->default !== false ) {

                if (  $this->type === "timestamp" ) {
                    $syntax .= " DEFAULT {$this->default} ";
                } elseif ( $this->default === null ) {
                    $syntax .= " DEFAULT NULL ";
                } else {
                    $syntax .= " DEFAULT '{$this->default}' ";
                }
            }

            return $syntax;
        }

        public function stringifyWithIncrement() {

            $syntax = "`{$this->name}` {$this->type}";
            $syntax .= $this->length !== false ? "({$this->length})" : " ";

            if ( $this->enumOptions ) {
                $syntax .= "('" . implode( "','", $this->enumOptions ) . "')";
            }

            $syntax .= $this->unsigned ? " UNSIGNED " : "";
            $syntax .= $this->null ? " NULL " : " NOT NULL ";
            $syntax .= $this->increment ? " AUTO_INCREMENT " : "";

            return $syntax;
        }

    }
