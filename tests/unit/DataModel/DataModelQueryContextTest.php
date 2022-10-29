<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Select;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\DataModel;

class Example extends DataModel
{
    #[ LowerCase ]
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

class Example2 extends DataModel
{
    #[ LowerCase ]
    public mixed $NAME;

    #[ LowerCase ]
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
    public function testContext(): void
    {
        $ident = new Identifier('NAME');
        static::assertSame('NAME', $ident->stringify());

        $ident->addDataModelContext(new Example());
        static::assertSame('name', $ident->stringify());
    }

    public function testContextFQN(): void
    {
        $ident = new Identifier('schema', 'table', 'NAME');
        static::assertSame('schema.table.NAME', $ident->stringify());

        $ident = new Identifier('public', 'Example', 'NAME');
        $ident->addDataModelContext(new Example());
        static::assertSame('public.Example.name', $ident->stringify());
    }

    public function testPassingDownContext(): void
    {
        $select = new Select();
        $select->addDataModelContext(new Example());

        $select->add(new Identifier('NAME'));
        static::assertSame('SELECT name', $select->stringify());

        // adding context after creating children
        $select = new Select();
        $select->add(new Identifier('NAME'));

        $select->addDataModelContext(new Example());

        static::assertSame('SELECT name', $select->stringify());

        // adding context after creating children
        $select = new Select();
        $select->add(new Identifier('Example2', 'NAME'));

        $select->addDataModelContext(new Example2());
        static::assertSame('SELECT Example2.name', $select->stringify());
    }

    public function testMultipleContextAmbiguous(): void
    {
        $this->expectExceptionObject(new Exception('ambiguous'));

        $select = new Select();
        $select->addDataModelContext(new Example());
        $select->addDataModelContext(new Example2());

        $select->add(new Identifier('NAME'));
        static::assertSame('SELECT name', $select->stringify());
    }

    public function testMultipleContext(): void
    {
        $select = new Select();
        $select->addDataModelContext(new Example());
        $select->addDataModelContext(new Example2());

        $select->add(new Identifier('exampleId'));
        static::assertSame('SELECT exampleid', $select->stringify());
    }
}
