<?php

    namespace Wrapped\_\DataModel;

    use \Wrapped\_\Database\Facade\JoinBuilder;
    use \Wrapped\_\Database\Facade\QueryBuilder;
    use \Wrapped\_\Database\SQL\Clause\GroupBy;
    use \Wrapped\_\Database\SQL\Clause\Join;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;

    class DataModelQueryBuilder
    extends QueryBuilder {

        protected DataModel $prototype;

        protected $context = [];

        protected bool $disableAutomaticGroupBy = false;

        public function __construct( DataModel $prototype ) {

            parent::__construct( $prototype->getDatabase() );

            $this->prototype = $prototype;
            $this->addContext( $prototype );
        }

        public function count( $table, $what = '*', bool $distinct = false ): static {
            $this->disableAutomaticGroupBy();
            return parent::count( $table, $what, $distinct );
        }

        public function disableAutomaticGroupBy( bool $bool = true ): static {
            $this->disableAutomaticGroupBy = $bool;
            return $this;
        }

        public function addContext( DataModel $context ): static {

            $classesInContext = array_map( fn( $o ) => $o::class, $this->context );

            if ( !in_array( $context::class, $classesInContext ) ) {
                $this->context[] = $context;
            }

            return $this;
        }

        public function run() {

            foreach ( $this->context as $context ) {
                $this->stmt->addDataModelContext( $context );
            }

            if ( !$this->disableAutomaticGroupBy && $this->stmt->getCommand() instanceof Select ) {

                // checks if a join is present, than we need the group by pk
                if ( in_array( Join::class, array_map( fn( $q ) => $q::class, $this->stmt->getChildren() ) ) ) {
                    $this->stmt->add(
                        new GroupBy(
                            new Identifier(
                                $this->prototype::getSchemaName(),
                                $this->prototype::getTableName(),
                                $this->prototype->getPrimaryKey(),
                            )
                        )
                    );
                }
            }

            return parent::run();
        }

        public function with( DataModel $dest, callable $callback = null ): DataModelQueryBuilder {

            if ( !$callback ) {
                $callback = array_values( array_filter( array_map( fn( $c ) => $c::fetchPredefinedJoins( $dest::class ), $this->context ) ) )[0] ?? null;
            }

            $join = $callback( new JoinBuilder( $dest::getSchemaName(), $dest::getTableName() ) );

            $this->fetchStatement()->add(
                $join->fetchJoinClause()
            );

            $this->addContext( $dest );

            return $this;
        }

        public function get( ?Collection $overrideInstance = null ): Collection {

            if ( $overrideInstance ) {
                return $overrideInstance::buildFromQuery( $this->prototype, $this );
            }

            return Collection::buildFromQuery( $this->prototype, $this );
        }

    }
