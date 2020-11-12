<?php

    namespace Wrapped\_\DataModel;

    use \PDO;
    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Database\SQL\Clause\CTE;
    use \Wrapped\_\Database\SQL\Clause\From;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Insert;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Command\Update;
    use \Wrapped\_\Database\SQL\Expression\Bracket;
    use \Wrapped\_\Database\SQL\Expression\CaseWhen;
    use \Wrapped\_\Database\SQL\Expression\Cast;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Primitive;
    use \Wrapped\_\Database\SQL\Expression\SqlFunction;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TreeDataModel;
    use \Wrapped\_\Exception\Database\DatabaseException;

    abstract class TreeDataModel
    extends DataModel {

        public ?int $id = null;

        public ?int $depth = null;

        public ?int $left = null;

        public ?int $right = null;

        public ?int $parentId = null;

        private $_after, $_before, $_under, $_atParentRight = true;

        static protected $_transactionInitiatorId = null;

        final public static function getPrimaryKey(): string {
            return "id";
        }

        public function getId(): ?int {
            return $this->id;
        }

        public function getDepth(): ?int {
            return $this->depth;
        }

        public function getLeft(): ?int {
            return $this->left;
        }

        public function getRight(): ?int {
            return $this->right;
        }

        public function getParentId(): ?int {
            return $this->parentId;
        }

        public function setId( ?int $id ) {
            $this->id = $id;
            return $this;
        }

        public function setDepth( ?int $depth ) {
            $this->depth = $depth;
            return $this;
        }

        public function setLeft( ?int $left ) {
            $this->left = $left;
            return $this;
        }

        public function setRight( ?int $right ) {
            $this->right = $right;
            return $this;
        }

        public function setParentId( ?int $parentId ) {
            $this->parentId = $parentId;
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

            $sql1 = "DELETE FROM {$tableName} WHERE {$databaseHandle->quoteIdentifier( 'left' )} between {$this->left} and {$this->right}";
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

            $this->_under         = $parent;
            $this->_atParentRight = $atEnd;

            return $this;
        }

        protected function prepareDataForStorage( bool $includeNonFuzzy = false ): array {

            $result   = [];
            $skiplist = [ 'left', 'right', 'depth', 'parentId' ];

            foreach ( (new DataModelAnalyser( $this ) )->fetchPropertyAttributes() as $attribute ) {

                // skip pk
                if ( static::getPrimaryKey() !== null && $attribute->getName() == static::getPrimaryKey() && $this->{static::getPrimaryKey()} === null ) {
                    continue;
                }

                if ( in_array( $attribute->getName(), $skiplist ) ) {
                    continue;
                }

                $data = $this->{ $attribute->getGetter() }();

                if ( !$includeNonFuzzy && !$this->_isPropertyFuzzy( $attribute->getName(), $data ) ) {
                    continue;
                }

                $result[$attribute->getNamingConvention()->getString()] = $this->dehydrateAttribute( $attribute );
            }

            return $result;
        }

        /**
         * generates the insert part for the cte used to save new instances
         * @return Insert
         */
        protected function generateInsertCommand( string $datasource = '_bounds' ): Insert {

            return (new Insert( new Identifier( static::getSchemaName(), static::getTableName() ) ) )
                    ->add( ... array_map( fn( $i ) => new Identifier( $i ), array_keys( $this->prepareDataForStorage( true ) ) ) )
                    ->add( new Identifier( 'left' ) )
                    ->add( new Identifier( 'right' ) )
                    ->add( new Identifier( 'depth' ) )
                    ->add( new Identifier( 'parent_id' ) )
                    ->addQuery(
                        (new Statement(
                            (new Select() )
                            ->add( ... array_map( fn( $i ) => new Value( $i ), array_values( $this->prepareDataForStorage( true ) ) ) )
                            ->add( new Identifier( '_left' ) )
                            ->add( new Identifier( '_right' ) )
                            ->add( new Identifier( '_depth' ) )
                            ->add( new Identifier( '_parent_id' ) )
                        )
                        )
                        ->add( new From( new Identifier( $datasource ) ) )
            );
        }

        /**
         * fetches max( right ) from the tree
         * @param CTE $cte
         */
        protected function appendBoundsSelect( CTE $cte ) {
            $cte->with(
                new Identifier( '_bounds' ),
                (new Statement(
                        (new Select() )
                        ->add(
                            (new Expression() )
                            ->add(
                                (new SqlFunction(
                                    new Identifier( 'coalesce' ),
                                    new SqlFunction( new Identifier( 'max' ), new Identifier( 'right' ) ),
                                    new Value( 0 )
                                ) )
                            )
                            ->add( new Operator( '+' ) )
                            ->add( new Value( 1 ) )
                            ->addAlias( new Identifier( '_left' ) )
                        )
                        ->add(
                            (new Expression() )
                            ->add(
                                (new SqlFunction(
                                    new Identifier( 'coalesce' ),
                                    new SqlFunction( new Identifier( 'max' ), new Identifier( 'right' ) ),
                                    new Value( 0 )
                                ) )
                            )
                            ->add( new Operator( '+' ) )
                            ->add( new Value( 2 ) )
                            ->addAlias( new Identifier( '_right' ) )
                        )
                        ->add(
                            (new Expression(
                                new Value( 0 ), new Cast( 'int' )
                            ) )->addAlias( new Identifier( '_depth' ) )
                        )
                        ->add(
                            (new Expression(
                                new Primitive( null ), new Cast( 'int' )
                            ) )->addAlias( new Identifier( '_parent_id' ) )
                        )
                    )
                    )
                    ->add( new From( new Identifier( static::getSchemaName(), static::getTableName() ) ) )
            );
        }

        /**
         * updates the tree to insert a new child
         *
         *  under:
         *  with
         *  _parent as (
         *    select id,lft,rgt,depth
         *      from tree
         *     where id = 1
         *  )
         *  ,
         *  _widen_nodes_right as (
         *      update tree
         *         set
         *             lft = CASE WHEN lft > ( select lft from _parent ) THEN lft + 2 ELSE lft END,
         *             rgt = rgt + 2
         *        where rgt > ( select lft from _parent )
         *  )
         *  insert into tree ( lft, rgt, parentId, depth )
         *  select lft + 1, lft + 2, id, depth + 1
         *  from _parent;
         *
         * @param CTE $cte
         */
        public function appendUnderSelect( CTE $cte ) {

            $parentId = $this->_under->getId();
            // parent
            $cte->with(
                new Identifier( '_parent' ),
                (new Statement(
                        new Select(
                            (new Identifier( 'left' ) )->addAlias( new Identifier( '_left_old' ) ),
                            (new Expression( new Identifier( 'left' ), new Operator( "+" ), new Value( 1 ) ) )->addAlias( new Identifier( '_left' ) ),
                            (new Expression( new Identifier( 'left' ), new Operator( "+" ), new Value( 2 ) ) )->addAlias( new Identifier( '_right' ) ),
                            (new Expression( new Identifier( 'depth' ), new Operator( "+" ), new Value( 1 ) ) )->addAlias( new Identifier( '_depth' ) ),
                            (new Expression( new Identifier( 'id' ) ) )->addAlias( new Identifier( '_parent_id' ) ),
                        )
                    ) )
                    ->add( new From( new Identifier( static::getSchemaName(), static::getTableName() ) ) )
                    ->add( new Where( new Expression(
                                new Identifier( 'id' ),
                                new Operator( "=" ),
                                new Value( $parentId )
                        ) ) )
            );

            // update other nodes
            $cte->with(
                new Identifier( '_widen_nodes_right' ),
                (new Statement(
                        (new Update( new Identifier( static::getSchemaName(), static::getTableName() ) ) )

                        // left
                        ->add(
                            new Identifier( 'left' ),
                            new Expression(
                                (new CaseWhen() )
                                ->when(
                                    (new Expression(
                                        new Identifier( 'left' ),
                                        new Operator( ">" ),
                                        (new Bracket )
                                        ->add(
                                            (new Statement(
                                                new Select( new Identifier( '_left_old' ) )
                                            ) )
                                            ->add(
                                                new From( new Identifier( "_parent" ) )
                                            )
                                        )
                                    ) ),
                                    (new Expression(
                                        new Identifier( 'left' ),
                                        new Operator( "+" ),
                                        new Value( 2 )
                                    ) ),
                                )
                                ->else( new Identifier( 'left' ) )
                            )
                        )
                        // right
                        ->add(
                            new Identifier( 'right' ),
                            (new Expression(
                                new Identifier( 'right' ),
                                new Operator( "+" ),
                                new Value( 2 )
                        ) ) )
                    ) )
                    ->add(
                        new Where(
                            new Expression(
                                new Identifier( 'right' ),
                                new Operator( '>' ),
                                (new Bracket )
                                ->add(
                                    (new Statement(
                                        new Select( new Identifier( '_left_old' ) )
                                    ) )
                                    ->add(
                                        new From( new Identifier( "_parent" ) )
                                    )
                                ) )
                    ) )
            );
        }

        /**
         *
         * @return static|boolean
         * @throws Exception
         */
        public function save(): static {

            // update or insert?
            if ( !$this->_isPropertyFuzzy( static::getPrimaryKey(), $this->{static::getPrimaryKey()} ) ) {
                return $this->saveToDatabase();
            }

            $query = static::buildQuery();

            $specialColumns = [
                'left',
                'right',
                'depth',
                'parent_id',
            ];

            $cte = new CTE();
            $query->stmt->add( $cte );

            if ( $this->_under ) {
                $this->appendUnderSelect( $cte );
                $query->stmt->setCommand( $this->generateInsertCommand( '_parent' ) );
            } else {
                $this->appendBoundsSelect( $cte );
                $query->stmt->setCommand( $this->generateInsertCommand( '_bounds' ) );
            }

            $query->returning( ... [
                static::getPrimaryKey(),
                ... $specialColumns
            ] );

            $this->initData( $query->fetch() );

            // clear out every movement operation

            $this->_after  = null;
            $this->_before = null;
            $this->_under  = null;

            return $this;
        }

        /**
         * just for clearance while writing
         * @return $this
         */
        public function move() {
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

            $columns = [];
            foreach ( static::createDataModelAnalyser()->fetchPropertyAttributes() as $col ) {
                $columns[] = 'parent.' . $col->getName();
            }

            $selectString = implode( ",", $columns );

            $query = "SELECT {$selectString}
                        FROM {$tableName} AS node, {$tableName} AS parent
                        WHERE node.{$databaseHandle->quoteIdentifier( 'left' )} BETWEEN parent.{$databaseHandle->quoteIdentifier( 'left' )} AND parent.{$databaseHandle->quoteIdentifier( 'right' )}
                        AND node.id = {$id}
                        ORDER BY parent.{$databaseHandle->quoteIdentifier( 'left' )}";


            return Collection::buildFromPdoResult( new static, $databaseHandle->query( $query ) );
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

            $columns = [];
            foreach ( static::fetchAnalyserObject()->fetchAllColumns() as $col ) {
                $columns[] = 'node.' . $col;
            }

            $selectString = implode( ",", $columns );

            $query = "SELECT
                        {$selectString}
                        GROUP_CONCAT(parent.\"{$field}\" SEPARATOR '/') as path
                        FROM
                             {$tableName} AS node,
                             {$tableName} AS parent
                        WHERE
                             node.{$databaseHandle->quoteIdentifier( 'left' )} BETWEEN parent.{$databaseHandle->quoteIdentifier( 'left' )} AND parent.{$databaseHandle->quoteIdentifier( 'right' )}

                        GROUP BY node.id
                        HAVING path = {$path}
                        ORDER BY node.{$databaseHandle->quoteIdentifier( 'left' )}, parent.{$databaseHandle->quoteIdentifier( 'left' )}";

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
        public function fetchChildren( $order = "left", $direction = "ASC", int $depth = null ) {

            $logic = DbLogic::create()
                ->where( "left", ">", $this->getLeft() )->addAnd()
                ->where( "right", "<", $this->getRight() )
                ->order( $order, $direction );

            if ( $depth !== null ) {
                $logic
                    ->addAnd()
                    ->where( "depth", "<=", $this->getDepth() + $depth );
            }

            return static::find(
                    $logic
            );
        }

        /**
         *
         * @return static[]
         */
        public function fetchChildrenInclusive( $order = "left", $direction = "ASC", int $depth = null ) {

            $logic = DbLogic::create()
                ->where( "left", ">=", $this->getLeft() )->addAnd()
                ->where( "right", "<=", $this->getRight() )
                ->order( $order, $direction );

            if ( $depth !== null ) {
                $logic
                    ->addAnd()
                    ->where( "depth", "<=", $this->getDepth() + $depth );
            }

            return static::find(
                    $logic
            );
        }

        public function fetchDirectChildren( $order = "left", $direction = "ASC" ) {

            return static::find(
                    DbLogic::create()
                        ->where( "parentid", "=", $this->getId() )
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
