<?php

    declare(strict_types = 1);

    namespace Wrapped\_\DataModel\Search;

    use \Wrapped\_\Database\Facade\QueryBuilder;
    use \Wrapped\_\Database\SQL\Clause\Order;
    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Expression\Bracket;
    use \Wrapped\_\Database\SQL\Expression\Conjunction;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\SqlFunction;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\DataModel\Collection;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\DataModelQueryBuilder;
    use \Wrapped\_\DataModel\Search\Searchable;

    class DataModelSearch {

        private DataModel $prototype;

        private $operator = 'ilike';

        public function __construct( Searchable $prototype ) {
            $this->prototype = $prototype;
        }

        /**
         * escape all like specific elements, like _ and % as well as backslashes
         * @param string $searchString
         * @return string
         */
        private function escapeLike( string $searchString ): string {
            return str_replace( [ '\\', '_', '%' ], [ '\\\\', '\\_', '\\%' ], $searchString );
        }

        protected function split( string $input ): array {

            // distinquish between "search term with spaces" and spaces
            // => will result in
            // [ 'search term with sapces', 'and', 'spaces' ]
            preg_match_all( '~(?:\"(.+)\"|(\S+))~', $input, $pieces, \PREG_PATTERN_ORDER );

            return array_values( array_filter( array_merge( $pieces[1], $pieces[2] ) ) );
        }

        public function buildQuery( string $searchString, QueryBuilder $query = null ): DataModelQueryBuilder {

            $query = $query ?: $this->prototype::buildSelectQuery();

            $pieces = $this->split( $this->escapeLike( $searchString ) );
            $fields = $this->prototype::getSearchFields();

            if ( count( $pieces ) === 0 ) {
                return $query;
            }

            if ( !isset( $query->where ) ) {
                $query->where = new Where( new Expression() );
                $query->stmt->add( $query->where );
            }

            $expression = $query->where->expression;

            for ( $pieceIndex = 0; $pieceIndex < count( $pieces ); $pieceIndex++ ) {

                if ( $expression->fetchLast() !== null && !($expression->fetchLast() instanceof Conjunction) ) {
                    $expression->add( new Conjunction( 'and' ) );
                }

                $bracket = new Bracket();

                for ( $fieldIndex = 0; $fieldIndex < count( $fields ); $fieldIndex++ ) {

                    $bracket->add( new Identifier( $this->prototype->getTableName(), $fields[$fieldIndex] ) );
                    $bracket->add( new Operator( $this->operator ) );
                    $bracket->add( new Value( "%{$pieces[$pieceIndex]}%" ) );

                    if ( $fieldIndex + 1 < count( $fields ) ) {
                        $bracket->add( new Conjunction( 'or' ) );
                    }
                }

                $expression->add( $bracket );
            }

            // identifier list
            $fieldIdentifier = array_map( fn( $f ) => new Identifier( $this->prototype->getTableName(), $f ), $fields );

            // distance expressions list
            $fieldExpressions = array_map( fn( Identifier $i ) => new Expression( $i, new Operator( '<->' ), new Value( $searchString ) ), $fieldIdentifier );

            $query->order = new Order();
            $query->order->add( new Expression( new SqlFunction( new Identifier( 'least' ), ... $fieldExpressions ) ), 'asc' );
            $query->stmt->add( $query->order );

            return $query;
        }

        public function search( string $searchString ): Collection {
            return $this->buildQuery( $searchString )->get();
        }

    }
