<?php

    namespace Wrapped\_\Database\Driver\Mysql;

    use \Wrapped\_\Database\Database;

    class SchemaVerifier {

        private $schema;
        private $informationsSchema;

        public function __construct( \Wrapped\_\Database\Driver\Mysql\Schema $schema, Database $database ) {

            $this->schema             = $schema;
            $this->informationsSchema = $database;
        }



    }
