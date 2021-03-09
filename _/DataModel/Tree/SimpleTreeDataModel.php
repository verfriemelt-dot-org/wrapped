<?php

    namespace verfriemelt\wrapped\_\DataModel\Tree;

    use \Exception;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\CTE;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\From;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Join;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Union;
    use \verfriemelt\wrapped\_\Database\SQL\Clause\Where;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Cast;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
    use \verfriemelt\wrapped\_\Database\SQL\Expression\Value;
    use \verfriemelt\wrapped\_\DataModel\Collection;
    use \verfriemelt\wrapped\_\DataModel\DataModel;

    abstract class SimpleTreeDataModel
    extends DataModel
    implements TreeDataModelInterface {

        public static function getParentProperty(): string {
            return 'parentId';
        }

        public function fetchChildCount(): int {
            return $this->fetchChildren()->count();
        }

        public function fetchChildren( $order = "left", $direction = "ASC", int $depth = null ): Collection {

            $parentProp  = static::createDataModelAnalyser()->fetchPropertyByName( static::getParentProperty() );
            $primaryProp = static::createDataModelAnalyser()->fetchPropertyByName( static::getPrimaryKey() );

            $cte = new CTE();
            $cte->recursive();

            $recursiveStatement = static::buildSelectQuery()->stmt;
            $recursiveStatement->getCommand()->add(
                (new Expression(
                    new Value( 1 ), new Cast( 'int' )
                ) )->as( new Identifier( '_depth' )
                )
            );

            $lowerSelect = static::buildSelectQuery()->stmt->getCommand();
            $lowerSelect->add(
                new Expression(
                    new Identifier( '_depth' ), new Cast( 'int' ), new Operator( "+" ), new Value( 1 )
                )
            );

            $cte->with(
                new Identifier( '_data' ),
                $recursiveStatement
                    ->add( new Where(
                            new Expression(
                                new Identifier( $parentProp->fetchDatabaseName() ),
                                new Operator( '=' ),
                                new Value( $this->{$primaryProp->getGetter()}() )
                            )
                        )
                    )
                    ->add( new Union )
                    ->add( $lowerSelect )
                    ->add( new From( new Identifier( static::getSchemaName(), static::getTableName() ) ) )
                    ->add( new Join(
                            new Identifier( '_data' ),
                            new Expression(
                                new Identifier( static::getSchemaName(), static::getTableName(), $parentProp->fetchDatabaseName() ),
                                new Operator( "=" ),
                                new Identifier( '_data', $primaryProp->fetchDatabaseName() ),
                            )
                    ) )
            );

            if ( $depth !== null ) {
                $recursiveStatement->add( new Where( new Expression( new Identifier( "_depth" ), new Operator( '<' ), new Value( $depth ) ) ) );
            }

            $query = static::buildQuery();
            $query->stmt->add( $cte );

            $query->select( ... array_map( fn( $i ) => $i[1], static::fetchSelectColumns() ) );
            $query->from( '_data' );

            $query->addContext( $this );

            return $query->get();
        }

        public function fetchChildrenInclusive( $order = "left", $direction = "ASC", int $depth = null ): Collection {
            return new Collection( [
                $this,
                ... $this->fetchChildren( $order, $direction, $depth )
                ] );
        }

        public function fetchDirectChildren( $order = "left", $direction = "ASC" ): Collection {
            return $this->fetchChildren( $order, $direction, depth: 1 );
        }

        public function fetchParent(): ?static {

            $parentProp = static::createDataModelAnalyser()->fetchPropertyByName( static::getParentProperty() );
            $parent     = $this->{$parentProp->getGetter()}();

            if ( $parent === null ) {
                return null;
            }

            return static::get( $parent );
        }

        public function fetchPath(): Collection {

        }

        public function isChildOf( TreeDataModelInterface $model ): bool {

        }

        public function move(): static {
            return $this;
        }

        public function under( TreeDataModelInterface $parent ): static {

            if ( !($parent instanceof $this) ) {
                throw new Exception( 'cannot mix models' );
            }

            $parentProp = static::createDataModelAnalyser()->fetchPropertyByName( static::getParentProperty() );
            $this->{$parentProp->getSetter()}( $parent->getId() );

            return $this;
        }

    }
