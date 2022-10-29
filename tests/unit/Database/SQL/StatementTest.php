
<?php

    use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Clause\ForUpdate;
use verfriemelt\wrapped\_\Database\SQL\Clause\From;
use verfriemelt\wrapped\_\Database\SQL\Clause\Where;
use verfriemelt\wrapped\_\Database\SQL\Command\Insert;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Command\Update;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\Database\SQL\Expression\Value;
use verfriemelt\wrapped\_\Database\SQL\Statement;

class StatementTest extends TestCase
{
    public function testMinimalSelect(): void
    {
        $statement = new Statement((new Select() )->add(new Value(true)));
        static::assertSame('SELECT true', $statement->stringify());
    }

    public function testNestedSelect(): void
    {
        $statement = new Statement(
            (new Select() )
                ->add(
                    (new Select() )
                    ->add(new Value(true))
                )
        );
        static::assertSame('SELECT ( SELECT true )', $statement->stringify());
    }

    public function testSimpleQuery(): void
    {
        $statement = new Statement(
            (new Select() )
                ->add(
                    new Identifier('column_a')
                )
        );

        $statement->add(new From(new Identifier('table')));
        $statement->add(new Where(new Value(true)));
        static::assertSame('SELECT column_a FROM table WHERE true', $statement->stringify());
    }

    public function testInsert(): void
    {
        $statement = new Statement(
            (new Insert(new Identifier('test')) )
                ->add(
                    new Identifier('column_a')
                )
        );
        $statement->add((new Select() )->add(new Value(true)));
        static::assertSame('INSERT INTO test ( column_a ) SELECT true', $statement->stringify());
    }

    public function testDataBindings(): void
    {
        $statement = new Statement(
            (new Select() )
                ->add(
                    (new Select() )
                    ->add(new Value(15))
                    ->add(new Value(1))
                )
        );

        static::assertSame([15, 1], array_values($statement->fetchBindings()));
    }

    public function testDataBindingsClause(): void
    {
        $statement = new Statement(
            (new Select() )
                ->add(
                    (new Select() )
                    ->add(new Value(15))
                    ->add(new Value(1))
                )
        );
        $statement->add(new Where(new Value(666)));

        static::assertTrue(in_array(666, array_values($statement->fetchBindings()), true));
        static::assertTrue(in_array(1, array_values($statement->fetchBindings()), true));
    }

    public function testForUpdateOnlyWithSelect(): void
    {
        self::expectExceptionMessage('SELECT');

        $stmt = new Statement(new Update(new Identifier('table')));
        $stmt->add(new ForUpdate());
    }

    public function testForUpdateWithSelect(): void
    {
        $stmt = new Statement(new Select(new Value(true)));
        $stmt->add(new From(new Identifier('table')));
        $stmt->add(new ForUpdate());

        static::assertSame('SELECT true FROM table FOR UPDATE', $stmt->stringify());
    }
}
