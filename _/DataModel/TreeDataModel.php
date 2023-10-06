<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel;

use PDO;
use verfriemelt\wrapped\_\Database\DbLogic;
use verfriemelt\wrapped\_\Database\SQL\Clause\CTE;
use verfriemelt\wrapped\_\Database\SQL\Clause\From;
use verfriemelt\wrapped\_\Database\SQL\Clause\Where;
use verfriemelt\wrapped\_\Database\SQL\Command\Insert;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Command\Update;
use verfriemelt\wrapped\_\Database\SQL\Expression\Bracket;
use verfriemelt\wrapped\_\Database\SQL\Expression\CaseWhen;
use verfriemelt\wrapped\_\Database\SQL\Expression\Cast;
use verfriemelt\wrapped\_\Database\SQL\Expression\Conjunction;
use verfriemelt\wrapped\_\Database\SQL\Expression\Expression;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Operator;
use verfriemelt\wrapped\_\Database\SQL\Expression\SqlFunction;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\Database\SQL\Statement;
use verfriemelt\wrapped\_\Exception\Database\DatabaseException;

abstract class TreeDataModel extends DataModel
{
    public ?int $id = null;

    public int $depth = 0;

    public ?int $left = null;

    public ?int $right = null;

    public ?int $parentId = null;

    final public const INSERT_AFTER = 'after';

    final public const INSERT_BEFORE = 'before';

    final public const INSERT_UNDER_LEFT = 'under_left';

    final public const INSERT_UNDER_RIGHT = 'under_right';

    private string $insertMode = self::INSERT_UNDER_LEFT;

    private ?TreeDataModel $insertPosition = null;

    protected static $_transactionInitiatorId;

    final public static function getPrimaryKey(): string
    {
        return 'id';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function getLeft(): ?int
    {
        return $this->left;
    }

    public function getRight(): ?int
    {
        return $this->right;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
        return $this;
    }

    public function setDepth(?int $depth)
    {
        $this->depth = $depth;
        return $this;
    }

    public function setLeft(?int $left)
    {
        $this->left = $left;
        return $this;
    }

    public function setRight(?int $right)
    {
        $this->right = $right;
        return $this;
    }

    public function setParentId(?int $parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * this deletes all children together with the node
     * be aware of funky features, if you're saving children after parents death!
     *
     * @return bool
     */
    public function delete(): static
    {
        $width = $this->right - $this->left + 1;

        // table
        $tableName = static::fetchTablename();
        $databaseHandle = static::fetchDatabase();

        $sql1 = "DELETE FROM {$tableName} WHERE {$databaseHandle->quoteIdentifier('left')} between {$this->left} and {$this->right}";
        $databaseHandle->query($sql1);

        $this->shiftLeft($this->right, -$width);
        $this->shiftRight($this->right, -$width);

        return parent::delete();
    }

    /**
     * checks if given movement is allowed
     *
     * @throws Exception
     * @throws DatabaseException
     */
    private function validateMove(TreeDataModel $moveTo)
    {
        if (!$moveTo instanceof $this) {
            throw new \Exception('illegal mix of items');
        }

        if ($this->id && $this->id === $moveTo->getId()) {
            throw new DatabaseException("cannot move model »{$this->id}« after »{$moveTo->getId()}«");
        }

        if (
            $moveTo->getLeft() > $this->left && $moveTo->getRight() < $this->right
        ) {
            throw new DatabaseException('cannot move model under itself');
        }
    }

    /**
     * inserts the new created instance after the given model
     * inherits parent and depth
     *
     * @return bool|static
     *
     * @throws Exception
     */
    public function after(TreeDataModel $after)
    {
        $this->validateMove($after);

        $this->insertMode = self::INSERT_AFTER;
        $this->insertPosition = $after;
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
     * @return bool|static
     *
     * @throws Exception
     */
    public function before(TreeDataModel $before)
    {
        $this->validateMove($before);

        $this->insertMode = self::INSERT_BEFORE;
        $this->insertPosition = $before;

        return $this;
    }

    /**
     * inserts item under parent
     * by default as the last item aligned to the $parent->getRight()
     *
     * @param type $parent
     *
     * @return $this
     */
    public function under(TreeDataModel $parent, $atEnd = true): static
    {
        $this->validateMove($parent);

        $this->insertMode = $atEnd ? self::INSERT_UNDER_RIGHT : self::INSERT_UNDER_LEFT;
        $this->insertPosition = $parent;

        return $this;
    }

    protected function prepareDataForStorage(bool $includeNonFuzzy = false): array
    {
        $result = [];
        $skiplist = ['left', 'right', 'depth', 'parentId'];

        foreach ((new DataModelAnalyser($this))->fetchProperties() as $attribute) {
            // skip pk
            if (static::getPrimaryKey() !== null && $attribute->getName() === static::getPrimaryKey(
            ) && $this->{static::getPrimaryKey()} === null) {
                continue;
            }

            if (in_array($attribute->getName(), $skiplist)) {
                continue;
            }

            $data = $this->{$attribute->getGetter()}();

            if (!$includeNonFuzzy && !$this->_isPropertyFuzzy($attribute->getName())) {
                continue;
            }

            $result[$attribute->fetchBackendName()] = $this->dehydrateProperty($attribute);
        }

        return $result;
    }

    /**
     * generates the insert part for the cte used to save new instances
     */
    protected function generateUpdateCommand(string $datasource = '_bounds'): Insert
    {
        $update = new Update(new Identifier(static::fetchSchemaname(), static::fetchTablename()));

        $update->add(new Identifier('left'), new Identifier('_left'));
        $update->add(new Identifier('right'), new Identifier('_right'));
        $update->add(new Identifier('depth'), new Identifier('_depth'));
        $update->add(new Identifier('parentId'), new Identifier('_parent_id'));

        foreach ($this->prepareDataForStorage() as $prop => $value) {
            $update->add(new Identifier($prop), new Value($value));
        }

        $update->add(new From(new Identifier($datasource)));

        return $update;
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
     */
    public function cteInsert()
    {
        $parentId = $this->insertPosition?->getId();
        $cte = new CTE();

        if ($parentId) {
            $cte->with(
                new Identifier('_move'),
                (new Statement(
                    new Select(
                        // _new_parent
                        (new CaseWhen(new Value($this->insertMode)))
                            ->when(
                                new Value(self::INSERT_UNDER_LEFT),
                                new Identifier('id')
                            )
                            ->when(
                                new Value(self::INSERT_UNDER_RIGHT),
                                new Identifier('id')
                            )
                            ->else(new Identifier('parentId'))
                            ->as(new Identifier('_parent_id')),
                        // _to_pos
                        (new CaseWhen(new Value($this->insertMode)))
                            ->when(
                                new Value(self::INSERT_BEFORE),
                                new Expression(new Identifier('left'))
                            )
                            ->when(
                                new Value(self::INSERT_UNDER_LEFT),
                                new Expression(new Identifier('left'), new Operator('+'), new Value(1))
                            )
                            ->when(
                                new Value(self::INSERT_UNDER_RIGHT),
                                new Identifier('right')
                            )
                            ->when(
                                new Value(self::INSERT_AFTER),
                                new Expression(new Identifier('right'), new Operator('+'), new Value(1)),
                            )
                            ->as(new Identifier('_to_pos')),
                        // depth
                        (new CaseWhen(new Value($this->insertMode)))
                            ->when(
                                new Value(self::INSERT_UNDER_LEFT),
                                new Expression(new Identifier('depth'), new Operator('+'), new Value(1))
                            )
                            ->when(
                                new Value(self::INSERT_UNDER_RIGHT),
                                new Expression(new Identifier('depth'), new Operator('+'), new Value(1))
                            )
                            ->else(new Identifier('depth'))
                            ->as(new Identifier('_depth'))
                    )
                ))
                    ->add(new From(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
                    ->add(
                        new Where(
                            new Expression(
                                new Identifier('id'),
                                new Operator('='),
                                new Value($parentId)
                            )
                        )
                    )
            );

            // update other nodes
            $cte->with(
                new Identifier('_widen_nodes_right'),
                (new Statement(
                    (new Update(new Identifier(static::fetchSchemaname(), static::fetchTablename())))

                        // left
                        ->add(
                            new Identifier('left'),
                            new Expression(
                                (new CaseWhen())
                                    ->when(
                                        new Expression(
                                            new Identifier('left'),
                                            new Operator('>='),
                                            (new Bracket())
                                                ->add(
                                                    (new Statement(
                                                        new Select(new Identifier('_to_pos'))
                                                    ))
                                                        ->add(
                                                            new From(new Identifier('_move'))
                                                        )
                                                )
                                        ),
                                        new Expression(
                                            new Identifier('left'),
                                            new Operator('+'),
                                            new Value(2)
                                        ),
                                    )
                                    ->else(new Identifier('left'))
                            )
                        )
                        // right
                        ->add(
                            new Identifier('right'),
                            new Expression(
                                new Identifier('right'),
                                new Operator('+'),
                                new Value(2)
                            )
                        )
                ))
                    ->add(
                        new Where(
                            new Expression(
                                new Identifier('right'),
                                new Operator('>='),
                                (new Bracket())
                                    ->add(
                                        (new Statement(
                                            new Select(new Identifier('_to_pos'))
                                        ))
                                            ->add(
                                                new From(new Identifier('_move'))
                                            )
                                    )
                            )
                        )
                    )
            );
        } else {
            $cte->with(
                new Identifier('_move'),
                (new Statement(
                    new Select(
                        (new Expression())
                            ->add(
                                new SqlFunction(
                                    new Identifier('coalesce'),
                                    new SqlFunction(new Identifier('max'), new Identifier('right')),
                                    new Value(0)
                                )
                            )
                            ->add(new Operator('+'))
                            ->add(new Value(1))
                            ->addAlias(new Identifier('_to_pos')),
                        // under
                        (new Expression(new Value(0), new Cast('int')))->addAlias(new Identifier('_depth')),
                        (new Expression(new Value(null), new Cast('int')))->addAlias(new Identifier('_parent_id')),
                    )
                ))
                    ->add(new From(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
            );
        }

        $insert = (new Insert(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
            ->add(...array_map(fn ($i) => new Identifier($i), array_keys($this->prepareDataForStorage(true))))
            ->add(new Identifier('left'))
            ->add(new Identifier('right'))
            ->add(new Identifier('depth'))
            ->add(new Identifier('parentId'))
            ->addQuery(
                (new Statement(
                    (new Select())
                        ->add(...array_map(fn ($i) => new Value($i), array_values($this->prepareDataForStorage(true))))
                        ->add(new Expression(new Identifier('_to_pos')))
                        ->add(new Expression(new Identifier('_to_pos'), new Operator('+'), new Value(1)))
                        ->add(new Identifier('_depth'))
                        ->add(new Identifier('_parent_id'))
                )
                )
                    ->add(new From(new Identifier('_move')))
            );

        return [$cte, $insert];
    }

    protected function cteMove(TreeDataModel $to)
    {
        $cte = new CTE();

        // boundary of current element
        $cte->with(
            new Identifier('_move'),
            (new Statement(
                new Select(
                    (new Identifier('left'))->as(new Identifier('_left')),
                    (new Identifier('right'))->as(new Identifier('_right')),
                    (new Identifier('depth'))->as(new Identifier('_depth'))
                )
            ))
                ->add(new From(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
                ->add(
                    new Where(
                        new Expression(
                            new Identifier(static::getPrimaryKey()),
                            new Operator('='),
                            new Value($this->{static::getPrimaryKey()})
                        )
                    )
                )
        );

        // move to
        $cte->with(
            new Identifier('_to'),
            (new Statement(
                new Select(
                    // parent id
                    (new CaseWhen(new Value($this->insertMode)))
                        ->when(
                            new Value(self::INSERT_UNDER_LEFT),
                            new Identifier('id')
                        )
                        ->when(
                            new Value(self::INSERT_UNDER_RIGHT),
                            new Identifier('id')
                        )
                        ->else(new Identifier('parentId'))
                        ->as(new Identifier('_new_parent')),
                    // new depth
                    (new CaseWhen())
                        ->when(
                            // same depth
                            new Expression(
                                new Bracket(
                                    new Statement(
                                        new Select(new Expression(new Identifier('_depth'))),
                                        new From(new Identifier('_move'))
                                    )
                                ),
                                new Operator('>='),
                                new Identifier('depth'),
                            ),
                            // substract when under -1
                            new Expression(
                                new Identifier('depth'),
                                new Operator('+'),
                                (new CaseWhen(new Value($this->insertMode)))
                                    ->when(
                                        new Value(self::INSERT_UNDER_LEFT),
                                        new Expression(new Value(1), new Cast('int'))
                                    )
                                    ->when(
                                        new Value(self::INSERT_UNDER_RIGHT),
                                        new Expression(new Value(1), new Cast('int'))
                                    )
                                    ->else(new Expression(new Value(0), new Cast('int'))),
                                new Operator('-'),
                                new Bracket(
                                    new Statement(
                                        new Select(new Expression(new Identifier('_depth'))),
                                        new From(new Identifier('_move'))
                                    )
                                )
                            )
                        )
                        ->else(
                            new Expression(
                                new Bracket(
                                    new Statement(
                                        new Select(new Expression(new Identifier('_depth'))),
                                        new From(new Identifier('_move'))
                                    )
                                ),
                                new Operator('+'),
                                new Identifier('depth'),
                                new Operator('+'),
                                (new CaseWhen(new Value($this->insertMode)))
                                    ->when(
                                        new Value(self::INSERT_UNDER_LEFT),
                                        new Expression(new Value(1), new Cast('int'))
                                    )
                                    ->when(
                                        new Value(self::INSERT_UNDER_RIGHT),
                                        new Expression(new Value(1), new Cast('int'))
                                    )
                                    ->else(new Expression(new Value(0), new Cast('int')))
                            )
                        )
                        ->as(new Identifier('_depth_diff')),
                    // new left
                    (new CaseWhen(new Value($this->insertMode)))
                        ->when(
                            new Value(self::INSERT_BEFORE),
                            new Expression(new Identifier('left'))
                        )
                        ->when(
                            new Value(self::INSERT_UNDER_LEFT),
                            new Expression(new Identifier('left'), new Operator('+'), new Value(1))
                        )
                        ->when(
                            new Value(self::INSERT_UNDER_RIGHT),
                            new Identifier('right')
                        )
                        ->when(
                            new Value(self::INSERT_AFTER),
                            new Expression(new Identifier('right'), new Operator('+'), new Value(1)),
                        )
                        ->as(new Identifier('_to_pos'))
                )
            ))
                ->add(new From(new Identifier(static::fetchSchemaname(), static::fetchTablename())))
                ->add(
                    new Where(
                        new Expression(
                            new Identifier(static::getPrimaryKey()),
                            new Operator('='),
                            new Value($to->{static::getPrimaryKey()})
                        )
                    )
                )
        );

        // move context
        $cte->with(
            new Identifier('_context'),
            new Statement(
                new Select(
                    (new Expression(
                        new Bracket(
                            new Statement(
                                new Select(
                                    new Expression(
                                        new Identifier('_right'),
                                        new Operator('-'),
                                        new Identifier('_left'),
                                        new Operator('+'),
                                        new Value(1)
                                    )
                                ),
                                new From(new Identifier('_move'))
                            )
                        )
                    ))->as(new Identifier('_width')),
                    (new CaseWhen())
                        ->when(
                            new Expression(
                                new Bracket(
                                    new Statement(
                                        new Select(new Identifier('_to_pos')),
                                        new From(new Identifier('_to'))
                                    )
                                ),
                                new Operator('<'),
                                new Bracket(
                                    new Statement(
                                        new Select(new Identifier('_left')),
                                        new From(new Identifier('_move'))
                                    )
                                ),
                            ),
                            new Expression(
                                new Bracket(
                                    new Statement(
                                        new Select(new Identifier('_left')),
                                        new From(new Identifier('_move'))
                                    )
                                ),
                                new Operator('-'),
                                new Bracket(
                                    new Statement(
                                        new Select(new Identifier('_to_pos')),
                                        new From(new Identifier('_to'))
                                    )
                                ),
                            ),
                        )
                        ->else(
                            new Expression(
                                new Bracket(
                                    new Bracket(
                                        new Statement(
                                            new Select(new Identifier('_to_pos')),
                                            new From(new Identifier('_to'))
                                        )
                                    ),
                                    new Operator('-'),
                                    new Bracket(
                                        new Statement(
                                            new Select(
                                                new Expression(
                                                    new Identifier('_right'),
                                                    new Operator('+'),
                                                    new Value(1)
                                                )
                                            ),
                                            new From(new Identifier('_move'))
                                        )
                                    )
                                ),
                                new Operator('*'),
                                new Value(-1)
                            ),
                        )
                        ->as(new Identifier('_distance')),
                    (new SqlFunction(
                        new Identifier('least'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ))->as(new Identifier('_left_min')),
                    (new SqlFunction(
                        new Identifier('greatest'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ))->as(new Identifier('_left_max'))
                )
            )
        );

        $upd = new Update(new Identifier(static::fetchSchemaname(), static::fetchTablename()));
        $upd->add(
            new Identifier('left'),
            (new CaseWhen())
                // make space to the left
                ->when(
                    new Expression(
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('>='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_min')),
                                new From(new Identifier('_context'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_max')),
                                new From(new Identifier('_context'))
                            )
                        ),
                    ),
                    (new Expression())
                        ->add(new Identifier('left'))
                        ->add(new Operator('+'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_width')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )
                // make space, to right
                ->when(
                    new Expression(
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Operator('>'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('>'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_max')),
                                new From(new Identifier('_context'))
                            )
                        )
                    ),
                    (new Expression())
                        ->add(new Identifier('left'))
                        ->add(new Operator('-'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_width')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )
                // update self
                ->when(
                    new Expression(
                        new Identifier('left'),
                        new Operator('>='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ),
                    (new Expression())
                        ->add(new Identifier('left'))
                        ->add(new Operator('-'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_distance')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )
                // rest
                ->else(new Identifier('left'))
        );

        $upd->add(
            new Identifier('right'),
            (new CaseWhen())
                // make space to the left
                ->when(
                    new Expression(
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('>='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_min')),
                                new From(new Identifier('_context'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_max')),
                                new From(new Identifier('_context'))
                            )
                        ),
                    ),
                    (new Expression())
                        ->add(new Identifier('right'))
                        ->add(new Operator('+'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_width')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )

                // update self
                ->when(
                    new Expression(
                        new Identifier('left'),
                        new Operator('>='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('left'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ),
                    (new Expression())
                        ->add(new Identifier('right'))
                        ->add(new Operator('-'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_distance')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )

                // make space, to right
                ->when(
                    new Expression(
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_to_pos')),
                                new From(new Identifier('_to'))
                            )
                        ),
                        new Operator('>'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('>'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('<'),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left_max')),
                                new From(new Identifier('_context'))
                            )
                        )
                    ),
                    (new Expression())
                        ->add(new Identifier('right'))
                        ->add(new Operator('-'))
                        ->add(
                            new Bracket(
                                new Statement(
                                    new Select(new Identifier('_width')),
                                    new From(new Identifier('_context'))
                                )
                            )
                        )
                )

                // rest
                ->else(new Identifier('right'))
        );

        // depth
        $upd->add(
            new Identifier('depth'),
            (new CaseWhen())
                // update new depth on moved branch
                ->when(
                    new Expression(
                        new Identifier('left'),
                        new Operator('>='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('<='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ),
                    (new Expression())
                        ->add(new Identifier('depth'))
                        ->add(new Operator('+'))
                        ->add(
                            new Expression(
                                new Bracket(
                                    new Statement(
                                        new Select(new Identifier('_depth_diff')),
                                        new From(new Identifier('_to'))
                                    )
                                )
                            )
                        )
                )
                ->else(new Identifier('depth'))
        );

        // update parent id
        $upd->add(
            new Identifier('parentId'),
            (new CaseWhen())
                // update new depth on moved branch
                ->when(
                    new Expression(
                        new Identifier('left'),
                        new Operator('='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_left')),
                                new From(new Identifier('_move'))
                            )
                        ),
                        new Conjunction('and'),
                        new Identifier('right'),
                        new Operator('='),
                        new Bracket(
                            new Statement(
                                new Select(new Identifier('_right')),
                                new From(new Identifier('_move'))
                            )
                        ),
                    ),
                    new Bracket(
                        new Statement(
                            new Select(new Identifier('_new_parent')),
                            new From(new Identifier('_to'))
                        )
                    )
                )
                ->else(new Identifier('parentId'))
        );

        return [$cte, $upd];
    }

    /**
     * @return static|bool
     *
     * @throws Exception
     */
    public function save(): static
    {
        if ($this->_isPropertyFuzzy(static::getPrimaryKey())) {
            $this->insertRecord();
        } else {
            $this->updateRecord();
        }

        // clear out every movement operation
        $this->insertPosition = null;
        $this->insertMode = self::INSERT_UNDER_RIGHT;

        return $this;
    }

    protected function updateRecord(): static
    {
        $isMovement = $this->insertPosition !== null;

        if (!$isMovement) {
            parent::updateRecord();
        } else {
            [$cte, $upd] = $this->cteMove($this->insertPosition);

            $query = static::buildQuery();
            $query->stmt->add($cte);
            $query->stmt->setCommand($upd);

            $query->addContext($this);

            $query->run();
        }

        return $this;
    }

    protected function insertRecord(): static
    {
        $query = static::buildQuery();

        $specialColumns = [
            'left',
            'right',
            'depth',
            'parentId',
        ];

        [$cte, $insert] = $this->cteInsert();

        $query->stmt->setCommand($insert);

        $query->stmt->add($cte);
        $query->returning(...[
            static::getPrimaryKey(),
            ...$specialColumns,
        ]);

        $query->addContext($this);
        $result = $query->fetch();

        if ($result === null) {
            throw new DatabaseException('no data returned');
        }

        return $this->initData($result);
    }

    /**
     * just for clearance while writing
     *
     * @return $this
     */
    public function move(): static
    {
        return $this;
    }

    /**
     * returns all the instances leading to this node, starting with its root node
     *
     * @return Collection<static>
     */
    public function fetchPath(): Collection
    {
        $id = $this->getId();

        $tableName = static::fetchTablename();
        $databaseHandle = static::fetchDatabase();

        $columns = [];
        foreach (static::createDataModelAnalyser()->fetchProperties() as $col) {
            $columns[] = 'parent.' . $col->getName();
        }

        $selectString = implode(',', $columns);

        $query = static::buildSelectQuery();

        $query->stmt->add(
            new Where(
                new Expression(
                    new Identifier('left'),
                    new Operator('<='),
                    new Value($this->getLeft()),
                    new Conjunction('and'),
                    new Identifier('right'),
                    new Operator('>='),
                    new Value($this->getLeft()),
                )
            )
        );

        $query->order([['left', 'asc']]);

        return Collection::buildFromQuery(new static(), $query);
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
     * @param type $path  eg. /1/1.2/1.2.1
     * @param type $field eg. name
     *
     * @throws Exception
     */
    public static function fetchByPath($path, $field): ?static
    {
        if (!in_array(
            $field,
            array_map(fn (DataModelProperty $p) => $p->getName(), static::createDataModelAnalyser()->fetchProperties())
        )) {
            throw new \Exception('invalid field specified');
        }

        $tableName = static::fetchTablename();
        $databaseHandle = static::fetchDatabase();

        $path = $databaseHandle->fetchConnectionHandle()->quote($path);

        $columns = [];
        foreach (static::createDataModelAnalyser()->fetchProperties() as $col) {
            $columns[] = 'node.' . $col->fetchBackendName();
        }

        $selectString = implode(',', $columns);

        $query = "SELECT
                        {$selectString}
                        GROUP_CONCAT(parent.\"{$field}\" SEPARATOR '/') as path
                        FROM
                             {$tableName} AS node,
                             {$tableName} AS parent
                        WHERE
                             node.{$databaseHandle->quoteIdentifier('left')} BETWEEN parent.{$databaseHandle->quoteIdentifier('left')} AND parent.{$databaseHandle->quoteIdentifier('right')}

                        GROUP BY node.id
                        HAVING path = {$path}
                        ORDER BY node.{$databaseHandle->quoteIdentifier('left')}, parent.{$databaseHandle->quoteIdentifier('left')}";

        $result = $databaseHandle->query($query)->fetch(PDO::FETCH_ASSOC);

        if (!is_array($result)) {
            return null;
        }

        return (new static())->initData($result);
    }

    /**
     * @return Collection<static>
     */
    public function fetchChildren(string $order = 'left', string $direction = 'ASC', ?int $depth = null): Collection
    {
        $logic = DbLogic::create()
            ->where('left', '>', $this->getLeft())->addAnd()
            ->where('right', '<', $this->getRight())
            ->order($order, $direction);

        if ($depth !== null) {
            $logic
                ->addAnd()
                ->where('depth', '<=', $this->getDepth() + $depth);
        }

        return static::find(
            $logic
        );
    }

    /**
     * @return Collection<static>
     */
    public function fetchChildrenInclusive(
        string $order = 'left',
        string $direction = 'ASC',
        ?int $depth = null
    ): Collection {
        $logic = DbLogic::create()
            ->where('left', '>=', $this->getLeft())->addAnd()
            ->where('right', '<=', $this->getRight())
            ->order($order, $direction);

        if ($depth !== null) {
            $logic
                ->addAnd()
                ->where('depth', '<=', $this->getDepth() + $depth);
        }

        return static::find($logic);
    }

    /**
     * @return Collection<static>
     */
    public function fetchDirectChildren(string $order = 'left', string $direction = 'ASC'): Collection
    {
        return static::find(
            DbLogic::create()
                ->where('parentId', '=', $this->getId())
                ->order($order, $direction)
        );
    }

    /**
     * is the current node a child of the given node
     */
    public function isChildOf(TreeDataModel $model): bool
    {
        return $this->getRight() < $model->getRight() && $this->getLeft() > $model->getLeft();
    }

    /**
     * return the number of children
     */
    public function fetchChildCount(): int
    {
        return $this->fetchChildren()->count();
    }

    public function fetchParent(): ?static
    {
        if (!$this->parentId) {
            return null;
        }

        return static::get($this->parentId);
    }
}
