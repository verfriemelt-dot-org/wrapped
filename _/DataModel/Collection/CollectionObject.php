<?php

    namespace Wrapped\_\DataModel\Collection;

    use \Exception;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\DataModel\DataModel;

    class CollectionObject {

        private $model;
        private $dbLogic;
        private $parentCollectionObject = null;
        private $collection             = null;
        private $with                   = [];
        private $joins                  = [];
        private $currentJoin            = null;
        private $overrideSelection      = null;

        public function __construct( DataModel $model ) {
            $this->model = $model;
        }

        /**
         *
         * @return DataModel
         */
        public function fetchModel() {
            return $this->model;
        }

        /**
         *
         * @param Collection $collection
         * @return CollectionObject
         */
        public function setCollection( Collection $collection ) {
            $this->collection = $collection;
            return $this;
        }

        /**
         *
         * @param type $object
         * @param type $autoDetect
         * @return CollectionObject
         */
        public function with( $model, $autoDetect = true ) {

            if ( !$model instanceof DataModel && !class_exists( $model ) ) {
                throw new DatabaseException( "Illegal Datamodel passed {$model}" );
            }

            $collectionObject                         = new static( $model instanceof DataModel ? $model : new $model );
            $collectionObject->parentCollectionObject = $this;

            $join = new CollectionJoin( $this, $collectionObject );

            if ( $autoDetect ) {
                $collectionObject->autoDetectJoin( $join );
            }

            $collectionObject->currentJoin = $join;

            $this->with[]  = $collectionObject;
            $this->joins[] = $join;

            return $collectionObject;
        }

        /**
         *
         * @return CollectionObject[]
         */
        public function getWith() {
            return $this->with;
        }

        /**
         * pass new DbLogic object
         * @return DbLogic Logic Object
         */
        public function setLogic( DbLogic $dbLogic ) {
            $this->dbLogic = $dbLogic;
            return $this;
        }

        /**
         *
         * @return DbLogic
         */
        public function createLogic() {
            // local copy for old php 5.6 support
            $model = $this->model;
            $this->setLogic( (new DbLogic() )->setTableName( $model::getTableName() ) );
            return $this->getDbLogic();
        }

        /**
         * @return DbLogic Logic Object
         */
        public function getDbLogic() {
            return $this->dbLogic;
        }

        /**
         *
         * @return CollectionJoin[]
         */
        public function getJoins() {
            return $this->joins;
        }

        /**
         * tries to autododetect the joins on matching column names
         * eg. user.Id and userRelation.userId
         * @throws Exception
         */
        public function autoDetectJoin( CollectionJoin $join ) {

            if ( $this->_findJointWithParent( $join ) === false ) {
                $local  = $this->fetchModel();
                $parent = $this->parentCollectionObject->fetchModel();
                throw new Exception( "i could't find any joins between " . $local::getTableName() . " and " . $parent::getTableName() );
            }

            return true;
        }

        private function _findJointWithParent( CollectionJoin $join ) {

            if ( $this->_checkForPair( $join, $this->parentCollectionObject, $this, "Id" ) !== false ) {
                return true;
            }

            if ( $this->_checkForPair( $join, $this, $this->parentCollectionObject, "Id" ) !== false ) {
                return true;
            }

            return !empty( $this->joins );
        }

        /**
         *
         * @param \Wrapped\_\DataModel\Collection\CollectionJoin $join
         * @param \Wrapped\_\DataModel\Collection\CollectionObject $source
         * @param \Wrapped\_\DataModel\Collection\CollectionObject $dest
         * @param type $columnName column name to probe for, usually id
         * @return boolean
         */
        private function _checkForPair( CollectionJoin $join, CollectionObject $source, CollectionObject $dest, $columnName ) {

            $sourceAnalyser = new \Wrapped\_\ObjectAnalyser($source->fetchModel());
            $destAnalyser = new \Wrapped\_\ObjectAnalyser($dest->fetchModel());

            // sources shoud have classFoo::id
            // so source has getId
            // dest should have classBar::getClassFooId
            // so dest hast getClassFooId

            $methodNameSource = "get" . $columnName;
            $methodNameDest = "get" .  $destAnalyser->getObjectShortName() . $columnName;

            if ( $destAnalyser->findMethodByName( $methodNameSource ) && $sourceAnalyser->findMethodByName( $methodNameDest ) ) {

                $join->onSource( $columnName );
                $join->onDestination( lcfirst( $destAnalyser->getObjectShortName()) . $columnName );

                return true;
            }

            return false;
        }

        /**
         * used to append it to its parent, only internal use
         * @param \Wrapped\_\DataModel\Collection\CollectionObject $co
         * @return $this
         */
        public function setParent( CollectionObject $co ) {
            $this->parentCollectionObject = $co;
            return $this;
        }

        /**
         *
         * @param Array $override eg ["id"]
         * @return $this
         */
        public function setSelectionColumnOverride( $override ) {
            $this->overrideSelection = $override;
            return $this;
        }

        public function getSelectionColumns() {

            if ( $this->overrideSelection !== null ) {
                return $this->overrideSelection;
            }

            $selectColumns = [];

            foreach ( $this->model::fetchAnalyserObject()->fetchColumnsWithGetters() as $colum ) {
                $selectColumns[] = $colum["column"];
            }

            return $selectColumns;
        }

        /**
         *
         * @return CollectionJoin
         */
        public function fetchLastJoinObject() {
            return $this->currentJoin;
        }

    }
