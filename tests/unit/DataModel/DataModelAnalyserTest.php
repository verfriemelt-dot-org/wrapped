<?php

declare(strict_types=1);

namespace extraNamespace;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
use verfriemelt\wrapped\_\DataModel\DataModel;
use verfriemelt\wrapped\_\DataModel\DataModelAnalyser;
use verfriemelt\wrapped\_\DateTime\DateTime;

class Example extends DataModel
{
    public ?int $id = null;

    public ?string $complexFieldName = null;

    public ?DateTime $typed = null;

    #[SnakeCase]
    public ?string $complexFieldNameSnakeCase = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComplexFieldName(): ?string
    {
        return $this->complexFieldName;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setComplexFieldName(?string $complexFieldName): static
    {
        $this->complexFieldName = $complexFieldName;
        return $this;
    }

    public function getTyped(): ?DateTime
    {
        return $this->typed;
    }

    public function setTyped(?DateTime $typed): static
    {
        $this->typed = $typed;
        return $this;
    }

    public function getComplexFieldNameSnakeCase(): ?string
    {
        return $this->complexFieldNameSnakeCase;
    }

    public function setComplexFieldNameSnakeCase(?string $complexFieldNameSnakeCase): static
    {
        $this->complexFieldNameSnakeCase = $complexFieldNameSnakeCase;
        return $this;
    }
}

class DataModelAnalyserTest extends TestCase
{
    public function test_names(): void
    {
        $analyser = new DataModelAnalyser(new Example());

        static::assertSame('Example', $analyser->getBaseName());
        static::assertSame('extraNamespace\\Example', $analyser->getStaticName());
    }

    public function test_attributes(): void
    {
        $analyser = new DataModelAnalyser(new Example());

        static::assertSame(4, count($analyser->fetchProperties()), 'four valid attributes');

        // default naming convention snake case
        static::assertSame('id', $analyser->fetchProperties()[0]->getNamingConvention()->getString());
        static::assertSame('complex_field_name', $analyser->fetchProperties()[1]->getNamingConvention()->getString());
    }

    public function test_typed_attributes(): void
    {
        $analyser = new DataModelAnalyser(new Example());
        static::assertSame('typed', $analyser->fetchProperties()[2]->getNamingConvention()->getString());
        static::assertSame(DateTime::class, $analyser->fetchProperties()[2]->getType());

        static::assertTrue(class_exists($analyser->fetchProperties()[2]->getType()));
    }

    public function test_snake_case_convention(): void
    {
        $analyser = new DataModelAnalyser(new Example());
        static::assertSame(
            'complex_field_name_snake_case',
            $analyser->fetchProperties()[3]->getNamingConvention()->getString()
        );
    }
}
