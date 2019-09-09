<?php

    namespace Wrapped\_\DataModel\Collection;

    class CollectionJoin {

        private $source, $destination, $onSource, $onDestination, $operator = "=", $destCollectionObject, $sourceCollectionObject;

        public function __construct( CollectionObject $source, CollectionObject $destination ) {
            $this->source      = $source->fetchModel()::getTableName();
            $this->destination = $destination->fetchModel()::getTableName();

            $this->sourceCollectionObject = $source;
            $this->destCollectionObject   = $destination;
        }

        public function onSource( $on ) {
            $this->onSource = $on;
            return $this;
        }

        public function onDestination( $on ) {
            $this->onDestination = $on;
            return $this;
        }

        /**
         * build up the on string like user.id = userRole.userId
         * @return type
         */
        public function fetchOnString( \Wrapped\_\Database\Driver\DatabaseDriver $db ) {


            return "{$db->quoteIdentifier($this->source)}.{$db->quoteIdentifier($this->onSource)} {$this->operator} {$db->quoteIdentifier($this->destination)}.{$db->quoteIdentifier($this->onDestination)}";
        }

        public function setOperator( $op ) {
            $this->operator = $op;
            return $this;
        }

        public function getDestinationTable() {
            return $this->destination;
        }

        public function getSourceTable() {
            return $this->source;
        }

        /**
         *
         * @return CollectionObject
         */
        public function getDestinationCollectionObject() {
            return $this->destCollectionObject;
        }

        /**
         *
         * @return CollectionObject
         */
        public function getSourceCollectionObject() {
            return $this->sourceCollectionObject;
        }

    }
