<?php

    namespace Wrapped\_\DataModel;

    use \Exception;
    use \PDO;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Exception\Database\DatabaseException;

    abstract class TreeDataModel
    extends DataModel {

        static protected $_transactionInitiatorId = null;
        public $depth, $left, $right, $parentId, $id;
        private $_after, $_before, $_under, $_atParentRight                   = true;

        final protected static function _fetchMainAttribute(): string {
            return "id";
        }

        /**
         *
         * @return int
         */
        public function getDepth() {
            return $this->depth;
        }

        /**
         *
         * @return int
         */
        public function getLeft() {
            return $this->left;
        }

        /**
         *
         * @return int
         */
        public function getRight() {
            return $this->right;
        }

        /**
         *
         * @return int
         */
        public function getParentId() {
            return $this->parentId;
        }

        /**
         *
         * @param int $depth
         * @return static
         */
        public function setDepth( $depth ) {
            $this->depth = $depth;
            return $this;
        }

        /**
         *
         * @param int $left
         * @return static
         */
        public function setLeft( $left ) {
            $this->left = $left;
            return $this;
        }

        /**
         *
         * @param int $right
         * @return static
         */
        public function setRight( $right ) {
            $this->right = $right;
            return $this;
        }

        /**
         *
         * @param int $parentId
         * @return static
         */
        public function setParentId( $parentId ) {
            $this->parentId = $parentId;
            return $this;
        }

        /**
         *
         * @return int
         */
        public function getId() {
            return $this->id;
        }

        /**
         *
         * @param int $id
         * @return static
         */
        public function setId( $id ) {
            $this->id = $id;
            return $this;
        }

        /**
         * this deletes all children together with the node
         * be aware of funky features, if you're saving children after parents death!
         * @return boolean
         */
        public function delete() {

            $width = $this->right - $this->left + 1;

            // table
            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $sql1 = "DELETE FROM {$tableName} WHERE `left` between {$this->left} and {$this->right}";
            $databaseHandle->query( $sql1 );

            $this->shiftLeft( $this->right, -$width );
            $this->shiftRight( $this->right, -$width );

            return parent::delete();
        }

        /**
         * checks if given movement is allowed
         * @param TreeDataModel $moveTo
         * @throws Exception
         * @throws DatabaseException
         */
        private function validateMove( TreeDataModel $moveTo ) {

            if ( !$moveTo instanceof $this ) {
                throw new Exception( "illegal mix of items" );
            }

            if ( $this->id == $moveTo->getId() ) {
                throw new DatabaseException( "cannot move model after itself" );
            }

            if (
                $moveTo->getLeft() > $this->left &&
                $moveTo->getRight() < $this->right
            ) {
                throw new DatabaseException( "cannot move model under itself" );
            }
        }

        /**
         * inserts the new created instance after the given model
         * inherits parent and depth
         * @param static $after
         * @return boolean|static
         * @throws Exception
         */
        public function after( TreeDataModel $after ) {

            $this->validateMove( $after );

            $this->_after   = $after;
            $this->parentId = $after->getId();

            return $this;
        }

        /**
         * inserts the new created instance after the given model
         * inherits parent and depth
         *
         *            A
         *           /
         *          B
         *         / \
         *        C   D
         *
         *  insert before B will result in
         *             A
         *            / \
         *           E   B
         *              / \
         *             C   D
         *
         * @param static $before
         * @return boolean|static
         * @throws Exception
         */
        public function before( TreeDataModel $before ) {

            $this->validateMove( $before );

            $this->_before  = $before;
            $this->parentId = $before->getId();

            return $this;
        }

        /**
         * inserts item under parent
         * by default as the last item aligned to the $parent->getRight()
         *
         * @param type $parent
         * @return $this
         */
        public function under( TreeDataModel $parent, $atEnd = true ) {

            $this->validateMove( $parent );

            $this->parentId       = $parent->getId();
            $this->_under         = $parent;
            $this->_atParentRight = $atEnd;

            return $this;
        }

        /**
         *
         * @return static|boolean
         * @throws Exception
         */
        public function save() {

            $transactionInitiatorId = uniqid( "", true );

            // start transaction if no other is currently used
            if ( static::$_transactionInitiatorId === null ) {
                static::$_transactionInitiatorId = $transactionInitiatorId;
                static::getDatabase()->startTransaction();
                static::getDatabase()->connectionHandle->setAttribute( \PDO::ATTR_AUTOCOMMIT, 0 );
                static::getDatabase()->query( "LOCK TABLE `" . static::getTableName() . "` WRITE, `" . static::getTableName() . "` AS parent WRITE, `" . static::getTableName() . "` AS node WRITE" );
            }

            try {

                if ( $this->id === null || $this->_isPropertyFuzzy( "id", $this->id ) ) {
                    $this->_insert();
                } elseif ( $this->_isPropertyFuzzy( "parentId", $this->parentId ) ) {
                    $this->_move();
                }

                parent::save();

                // close transaction
                if ( static::$_transactionInitiatorId === $transactionInitiatorId ) {
                    static::getDatabase()->query( "UNLOCK TABLES" );
                    static::getDatabase()->commitTransaction();
                    static::getDatabase()->connectionHandle->setAttribute( \PDO::ATTR_AUTOCOMMIT, 1 );
                    static::$_transactionInitiatorId = null;
                }
            } catch ( \Exception $e ) {

                static::getDatabase()->rollbackTransaction();
                static::getDatabase()->query( "UNLOCK TABLES" );
                throw $e;
            }

            return $this;
        }

        /**
         * just for clearance while writing
         * @return $this
         */
        public function move() {
            return $this;
        }

        private function _flipNegative() {

            // table
            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $sql1 = "UPDATE {$tableName} SET `left` = -1*`left`, `right` = -1*`right` WHERE `left` between {$this->left} and {$this->right}";
            $databaseHandle->query( $sql1 );
        }

        private function _flipPositive() {
            // table
            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $sql1 = "UPDATE {$tableName} SET `left` = -1*`left`, `right` = -1*`right` WHERE `left` < 0";
            $databaseHandle->query( $sql1 );
        }

        /**
         * used for shifting flipped nodes into their places
         * @param type $amount
         * @return $this
         */
        private function shiftFlippedNodes( $amount ) {

            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $sql = "UPDATE {$tableName} set `left` = `left` + {$amount},`right` = `right` + {$amount} WHERE `left` < 0";
            $databaseHandle->query( $sql );

            return $this;
        }

        private function _move() {

            $this->_flipNegative();

            $width = $this->right - $this->left + 1;

            // shift left and rights on old neighbours
            $this->shiftLeft( $this->right, -$width );
            $this->shiftRight( $this->right, -$width );

            // shift left and rights for new parents
            if ( $this->_before ) {

                $this->_before->reload();

                // all inkl parents left
                $this->shiftLeft( $this->_before->left - 1, $width );
                $this->shiftRight( $this->_before->left - 1, $width );

                $newLeft     = $this->_before->left;
                $newParentId = $this->_before->parentId;
            } elseif ( $this->_after ) {

                $this->_after->reload();

                // all after parents right
                $this->shiftLeft( $this->_after->right, $width );
                $this->shiftRight( $this->_after->right, $width );

                $newLeft     = $this->_after->right + 1;
                $newParentId = $this->_after->parentId;
            } elseif ( $this->_under ) {

                $this->_under->reload();

                // excluding parents left
                $this->shiftLeft( $this->_under->left, $width );
                $this->shiftRight( $this->_under->left - 1, $width );

                $newLeft     = $this->_under->left + 1;
                $newParentId = $this->_under->id;
            }

            // move to new pos and flip again
            $this->shiftFlippedNodes( ($this->left - $newLeft ) );
            $this->_flipPositive();

            $this->reload();
            $this->setParentId( $newParentId );

            $oldDepth = $this->depth;

            // update depth
            $this->_generateDepth();

            $depthDiff = $this->depth - $oldDepth;

            foreach ( $this->fetchChildren() as $child ) {
                $child->setDepth( $child->getDepth() + $depthDiff );
                $child->save();
            }

            return $this;
        }

        /**
         *
         * @return static|boolean
         */
        private function _insert() {

            if ( $this->parentId !== null ) {

                // insert under parent
                $this->_insertUnderParent();
            } else {

                // no placed insert
                if ( $this->_after === null && $this->_before === null ) {

                    $this->_insertAsNewRoot();
                } else {

                    if ( $this->_after instanceof $this ) {

                        $this->_after->reload();
                        $alignment = $this->_after->getRight();

                        $this->setLeft( $alignment + 1 );
                        $this->setRight( $alignment + 2 );

                        $this->shiftLeft( $alignment, 2 );
                        $this->shiftRight( $alignment, 2 );
                    } else {

                        $this->_before->reload();
                        $alignment = $this->_before->getLeft();

                        $this->shiftLeft( $alignment - 1, 2 );
                        $this->shiftRight( $alignment - 1, 2 );

                        $this->setLeft( $alignment );
                        $this->setRight( $alignment + 1 );
                    }
                }
            }

            $this->_generateDepth();
            return $this;
        }

        private function _insertAsNewRoot() {

            $maxRightNode = static::findSingle( [], "right", "desc" );

            // when no object is present, we just set left and right to 1 and 2
            // otherwise get the current max right value
            $maxRight = $maxRightNode === null ? 0 : $maxRightNode->getRight();

            $this->setLeft( $maxRight + 1 );
            $this->setRight( $maxRight + 2 );
        }

        private function shiftLeft( $offset, $amount, $maxOffset = null ) {
            return $this->shift( $offset, $amount, "left", $maxOffset );
        }

        private function shiftRight( $offset, $amount, $maxOffset = null ) {
            return $this->shift( $offset, $amount, "right", $maxOffset );
        }

        /**
         *
         * @param int $offset
         * @param int $amount
         * @param enum $leftOrRight right,left
         * @return static
         */
        private function shift( int $offset, int $amount, string $leftOrRight = "left" ): TreeDataModel {

            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $databaseHandle->query(
                "UPDATE {$tableName}
                 SET `{$leftOrRight}` = `{$leftOrRight}` + {$amount}
                 WHERE `{$leftOrRight}` > {$offset}"
            );

            return $this;
        }

        /**
         * shifts the parent and child items accordingly to insert
         */
        private function _insertUnderParent() {

            $parent = static::get( $this->parentId );

            $myLeft = $this->_atParentRight ? $parent->getRight() : $parent->getLeft();

            $this->shiftLeft( $myLeft, 2 );
            $this->shiftRight( $myLeft - 1, 2 );

            if ( !$this->_atParentRight ) {
                $myLeft = $myLeft + 1;
            }

            // update myself
            $this->setLeft( $myLeft );
            $this->setRight( $myLeft + 1 );
        }

        /**
         *
         * @return static
         */
        private function _generateDepth() {

            if ( $this->parentId === null ) {
                return $this->setDepth( 0 );
            }

            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            // count sql query
            $query = "SELECT (COUNT(parent.id) - 1) AS depth
                        FROM {$tableName} AS node,
                                {$tableName} AS parent
                        WHERE node.`left` BETWEEN parent.`left` AND parent.`right`
                        and node.id = {$this->parentId}
                        GROUP BY node.id
                        ORDER BY node.`left`";

            $this->setDepth( $databaseHandle->query( $query )->fetch()["depth"] + 1 );

            return $this;
        }

        /**
         *
         * returns all the instances leading to this node, starting with its root node
         * @return \static[]
         * @throws Exception
         */
        public function fetchPath() {

            $id = $this->getId();

            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $query = "SELECT parent.*
                        FROM {$tableName} AS node, {$tableName} AS parent
                        WHERE node.`left` BETWEEN parent.`left` AND parent.`right`
                        AND node.id = {$id}
                        ORDER BY parent.`left`";


            return new Collection\CollectionResult( $databaseHandle->query( $query ), static::class );
        }

        /**
         * given that there is a tree
         *
         *         A
         *        / \
         *       B   C
         *          / \
         *         D   E
         *
         * and named:
         *  A 1
         *  B 1.1
         *  C 1.2
         *  D 1.2.1
         *  E 1.2.2
         *
         * you can fetch the Nodes by the Path
         *
         *    "1/1.2/1.2.1"
         *
         * and get Instances of A,C and D
         *
         * useful for menu struktures and you search by slugs like
         * /news/entry/static-page
         *
         * @param type $path eg. /1/1.2/1.2.1
         * @param type $field eg. name
         * @return boolean
         * @throws Exception
         */
        public static function fetchByPath( $path, $field ) {

            if ( !in_array( $field, static::fetchAnalyserObject()->fetchAllColumns() ) ) {
                throw new Exception( "invalid field specified" );
            }

            $tableName      = static::getTableName();
            $databaseHandle = static::getDatabase();

            $path = $databaseHandle->fetchConnectionHandle()->quote( $path );

            $query = "SELECT
                        node.*,
                        GROUP_CONCAT(parent.`{$field}` SEPARATOR '/') as path
                        FROM
                             {$tableName} AS node,
                             {$tableName} AS parent
                        WHERE
                             node.`left` BETWEEN parent.`left` AND parent.`right`

                        GROUP BY node.id
                        HAVING path = {$path}
                        ORDER BY node.`left`, parent.`left`";

            $res = $databaseHandle->query( $query );

            if ( $res->rowCount() == 0 ) {
                return false;
            }

            return (new static() )->initData( $res->fetch( PDO::FETCH_ASSOC ) );
        }

        /**
         * this is only used for converting existing tables to tree data model
         * we assume there is no sorting what so every, so everthing will be
         * left x , right x+1
         */
        public static function _convertOldToTreeData() {

            $count = 0;

            foreach ( static::all( "id" ) as $item ) {

                $item->setLeft( ++$count );
                $item->setRight( ++$count );
                $item->setDepth( 0 );
                $item->save();
            }
        }

        /**
         *
         * @return static[]
         */
        public function fetchChildren( $order = "left", $direction = "ASC" ) {

            return static::find(
                    DbLogic::create()
                        ->where( "left", ">", $this->getLeft() )->addAnd()
                        ->where( "right", "<", $this->getRight() )
                        ->order( $order, $direction )
            );
        }

        public function fetchDirectChildren( $order = "left", $direction = "ASC" ) {

            return static::find(
                    DbLogic::create()
                        ->where( "parentId", "=", $this->getId() )
                        ->order( $order, $direction )
            );
        }

        /**
         * is the current node a child of the given node
         * @param TreeDataModel $model
         * @return bool
         */
        public function isChildOf( TreeDataModel $model ): bool {

            return
                $this->getRight() < $model->getRight() &&
                $this->getLeft() > $model->getLeft();
        }

        /**
         * return the number of children
         * @return int
         */
        public function fetchChildCount(): int {
            return ($this->right - $this->left - 1) / 2;
        }

        /**
         * fetchs the current parrent
         * @return static
         */
        public function fetchParent() {
            if ( !$this->parentId ) {
                return null;
            }

            return static::get( $this->parentId );
        }

    }
