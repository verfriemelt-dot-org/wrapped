<?php

    namespace Wrapped\_\DataModel\Collection;

    use \Wrapped\_\Database\Driver\SQL\Table;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\Exception\Database\DatabaseException;

    class Collection {
        /* @var CollectionObject */

        private $mainCollectionObject;
        private $collectionResult;
        private $currentJoin = null;
        private $yieldMode = false;

        public function __construct( $model = null ) {

            if ( $model !== null ) {
                $this->from( $model );
            }
        }

        public function enableYieldmode( $bool = true ): CollectionObject {
            $this->yieldMode = $bool;
            return $this;
        }

        /**
         *
         * @param type $model
         * @return CollectionObject
         * @throws DatabaseException
         */
        public function from( $model ): CollectionObject {

            if ( !$model instanceof DataModel && !class_exists( $model ) ) {
                throw new DatabaseException( "Illegal Datamodel passed {$model}" );
            }

            $this->mainCollectionObject = new CollectionObject( $model instanceof DataModel ? $model : new $model );
            $this->mainCollectionObject->setCollection( $this );

            return $this->mainCollectionObject;
        }

        /**
         * fetch collection data
         * @return Collection
         */
        public function fetch(): Collection {

            if ( empty( $this->mainCollectionObject->getJoins() ) ) {
                $this->collectionResult = $this->simpleSelect();
                return $this;
            } else {
                $this->collectionResult = $this->join();
                return $this;
            }
        }

        /**
         *
         * @return CollectionResult
         */
        public function get(): CollectionResult {

            if ( $this->collectionResult === null ) {
                $this->fetch();
            }

            return $this->collectionResult;
        }

        private function simpleSelect() {

            $model   = $this->mainCollectionObject->fetchModel();
            $dbLogic = $this->mainCollectionObject->getDbLogic();

            $db    = $model::getDatabase();
            $table = $model::getTableName();

            $what = "`" . implode( "`, `", $model::fetchAnalyserObject()->fetchAllColumns() ) . "`";

            if ( $this->yieldMode ) {
                return new CollectionResultYield( $db->select( $table, $what, $dbLogic ), $model );
            } else {
                return new CollectionResult( $db->select( $table, $what, $dbLogic ), $model );
            }
        }

        private function join() {

            $tablename = $this->mainCollectionObject->fetchModel()::getTableName();
            $database  = $this->mainCollectionObject->fetchModel()::getDatabase();

            $table = $database->join( $tablename );
            $table->setSelectionColumns( $this->mainCollectionObject->getSelectionColumns() );

            $this->currentJoin = $table->getJoinHandle();

            //get associated objects
            $this->_readWith( $this->mainCollectionObject, $table );

            $res = $this->currentJoin->execute();

            if ( $this->yieldMode ) {
                return new CollectionResultYield( $res, $this->mainCollectionObject->fetchModel() );
            } else {
                return new CollectionResult( $res, $this->mainCollectionObject->fetchModel() );
            }
        }

        /**
         * here we parse to mysql joins
         * @param CollectionJoin $object
         * @param Table $table
         */
        private function _readJoins( CollectionJoin $join, Table $table ) {
            $table
                ->with( $join->getDestinationCollectionObject()->fetchModel()::getTableName(), $join->fetchOnString() )
                ->setSelectionColumns( $join->getDestinationCollectionObject()->getSelectionColumns() );
        }

        public function _readWith( CollectionObject $o, Table $table ) {

            $where = $o->getDbLogic();

            if ( $where !== null ) {
                $this->currentJoin->mergeDbLogic( $where );
            }

            // read joins
            foreach ( $o->getJoins() as $join ) {
                $this->_readJoins( $join, $table );
            }

            // recurse over other relations
            foreach ( $o->getWith() as $object ) {
                $this->_readWith( $object, $table );
            }
        }

    }
