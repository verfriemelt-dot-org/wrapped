<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

    use PDOStatement;
    use verfriemelt\wrapped\_\Database\Facade\JoinBuilder;
    use verfriemelt\wrapped\_\Database\Facade\QueryBuilder;
    use verfriemelt\wrapped\_\Database\SQL\Clause\GroupBy;
    use verfriemelt\wrapped\_\Database\SQL\Clause\Join;
    use verfriemelt\wrapped\_\Database\SQL\Command\Select;
    use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

    /**
     * @template T of DataModel
     */
    class DataModelQueryBuilder extends QueryBuilder
    {
        /**
         * @var T
         */
        protected DataModel $prototype;

        protected $context = [];

        protected bool $disableAutomaticGroupBy = false;

        /**
         * @param T $prototype
         */
        public function __construct(DataModel $prototype)
        {
            parent::__construct($prototype->fetchDatabase());

            $this->prototype = $prototype;
            $this->addContext($prototype);
        }

        public function count($table, $what = '*', bool $distinct = false): static
        {
            $this->disableAutomaticGroupBy();
            return parent::count($table, $what, $distinct);
        }

        public function disableAutomaticGroupBy(bool $bool = true): static
        {
            $this->disableAutomaticGroupBy = $bool;
            return $this;
        }

        public function addContext(DataModel $context): static
        {
            $classesInContext = array_map(fn ($o) => $o::class, $this->context);

            if (!in_array($context::class, $classesInContext)) {
                $this->context[] = $context;
            }

            return $this;
        }

        public function run(): PDOStatement
        {
            foreach ($this->context as $context) {
                $this->stmt->addDataModelContext($context);
            }

            if (!$this->disableAutomaticGroupBy && $this->stmt->getCommand() instanceof Select) {
                // checks if a join is present, than we need the group by pk
                if (in_array(Join::class, array_map(fn ($q) => $q::class, $this->stmt->getChildren()))) {
                    $this->stmt->add(
                        new GroupBy(
                            new Identifier(
                                $this->prototype::fetchSchemaname(),
                                $this->prototype::fetchTablename(),
                                $this->prototype->getPrimaryKey(),
                            )
                        )
                    );
                }
            }

            return parent::run();
        }

        /**
         * @return self<T>
         */
        public function with(DataModel $dest, callable $callback = null): self
        {
            if (!$callback) {
                $callback = array_values(array_filter(array_map(fn ($c) => $c::fetchPredefinedJoins($dest::class), $this->context)))[0] ?? null;
            }

            $join = $callback(new JoinBuilder($dest::fetchSchemaname(), $dest::fetchTablename()));

            $this->fetchStatement()->add(
                $join->fetchJoinClause()
            );

            $this->addContext($dest);

            return $this;
        }

        /**
         * @param Collection<T>|null $overrideInstance
         *
         * @return Collection<T>
         */
        public function get(?Collection $overrideInstance = null): Collection
        {
            if ($overrideInstance !== null) {
                return $overrideInstance::buildFromQuery($this->prototype, $this);
            }

            return Collection::buildFromQuery($this->prototype, $this);
        }
    }
