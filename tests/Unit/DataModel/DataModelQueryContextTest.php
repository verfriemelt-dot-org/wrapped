<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DataModel;

use Exception;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\DataModel;

class ContextExample extends DataModel
{
    #[LowerCase]
    public mixed $NAME;

    public mixed $id;

    public function getNAME(): mixed
    {
        return $this->NAME;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setNAME(mixed $NAME): static
    {
        $this->NAME = $NAME;
        return $this;
    }

    public function setId(mixed $id): static
    {
        $this->id = $id;
        return $this;
    }
}

class ContextExample2 extends DataModel
{
    #[LowerCase]
    public mixed $NAME;

    #[LowerCase]
    public mixed $exampleId;

    public mixed $id;

    public function getNAME(): mixed
    {
        return $this->NAME;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setNAME(mixed $NAME): static
    {
        $this->NAME = $NAME;
        return $this;
    }

    public function setId(mixed $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getExampleId(): mixed
    {
        return $this->exampleId;
    }

    public function setExampleId(mixed $exampleId): static
    {
        $this->exampleId = $exampleId;
        return $this;
    }
}

class DataModelQueryContextTest extends TestCase
{
    public function test_context(): void
    {
        $ident = new Identifier('NAME');
        static::assertSame('NAME', $ident->stringify());

        $ident->addDataModelContext(new ContextExample());
        static::assertSame('name', $ident->stringify());
    }

    public function test_context_fqn(): void
    {
        $ident = new Identifier('schema', 'table', 'NAME');
        static::assertSame('schema.table.NAME', $ident->stringify());

        $ident = new Identifier('public', 'ContextExample', 'NAME');
        $ident->addDataModelContext(new ContextExample());
        static::assertSame('public.ContextExample.name', $ident->stringify());
    }

    public function test_passing_down_context(): void
    {
        $select = new Select();
        $select->addDataModelContext(new ContextExample());

        $select->add(new Identifier('NAME'));
        static::assertSame('SELECT name', $select->stringify());

        // adding context after creating children
        $select = new Select();
        $select->add(new Identifier('NAME'));

        $select->addDataModelContext(new ContextExample());

        static::assertSame('SELECT name', $select->stringify());

        // adding context after creating children
        $select = new Select();
        $select->add(new Identifier('ContextExample2', 'NAME'));

        $select->addDataModelContext(new ContextExample2());
        static::assertSame('SELECT ContextExample2.name', $select->stringify());
    }

    public function test_multiple_context_ambiguous(): void
    {
        $this->expectExceptionObject(new Exception('ambiguous'));

        $select = new Select();
        $select->addDataModelContext(new ContextExample());
        $select->addDataModelContext(new ContextExample2());

        $select->add(new Identifier('NAME'));
        static::assertSame('SELECT name', $select->stringify());
    }

    public function test_multiple_context(): void
    {
        $select = new Select();
        $select->addDataModelContext(new ContextExample());
        $select->addDataModelContext(new ContextExample2());

        $select->add(new Identifier('exampleId'));
        static::assertSame('SELECT exampleid', $select->stringify());
    }
}
