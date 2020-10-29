<?php

    namespace Wrapped\_\DataModel;

    use \Wrapped\_\Database\Facade\QueryBuilder;
    use \Wrapped\_\Database\SQL\Clause\GroupBy;
    use \Wrapped\_\Database\SQL\Clause\Join;
    use \Wrapped\_\Database\SQL\Command\Select;
    use \Wrapped\_\Database\SQL\Expression\Identifier;

    class DataModelQueryBuilder
    extends QueryBuilder {

        protected DataModel $prototype;

        public function __construct( DataModel $prototype ) {

            parent::__construct( $prototype->getDatabase() );

            $this->prototype = $prototype;
        }

        public function run() {

            if ( $this->stmt->getCommand() instanceof Select ) {

                // checks if a join is present, than we need the group by pk
                if ( in_array( Join::class, array_map( fn( $q ) => $q::class, $this->stmt->getChildren() ) ) ) {
                    $this->stmt->add(
                        new GroupBy(
                            new Identifier(
                                $this->prototype::getSchemaName(),
                                $this->prototype::getTableName(),
                                $this->prototype->fetchPrimaryKey(),
                            )
                        )
                    );
                }
            }


            return parent::run();
        }

    }
