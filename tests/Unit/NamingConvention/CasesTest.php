<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\NamingConvention;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\LowerCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\PascalCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SnakeCase;
use verfriemelt\wrapped\_\DataModel\Attribute\Naming\SpaceCase;

class CasesTest extends TestCase
{
    public function test_space_case(): void
    {
        $case = new SpaceCase('test case experiment');
        static::assertSame(['test', 'case', 'experiment'], $case->fetchStringParts());

        $camelcase = $case->convertTo(CamelCase::class);

        static::assertSame('testCaseExperiment', $camelcase->getString());
    }

    public function test_lower_case(): void
    {
        $case = LowerCase::fromStringParts(...['space', 'seperated', 'text']);
        static::assertSame('spaceseperatedtext', $case->getString());

        $case = new SpaceCase('space seperated text');
        $lc = $case->convertTo(LowerCase::class);

        static::assertSame('spaceseperatedtext', $lc->getString());
    }

    public function test_camel_case(): void
    {
        $case = new CamelCase('thisIsSparta');

        static::assertSame(['this', 'is', 'sparta'], $case->fetchStringParts());
        static::assertSame('thisIsSparta', CamelCase::fromStringParts(...['this', 'is', 'sparta'])->getString());
    }

    public function test_pascal_case(): void
    {
        $case = new PascalCase('thisIsSparta');

        static::assertSame(['this', 'is', 'sparta'], $case->fetchStringParts());
        static::assertSame('ThisIsSparta', PascalCase::fromStringParts(...['this', 'is', 'sparta'])->getString());
    }

    public function test_conversion(): void
    {
        $case = (new CamelCase('complexFieldNameSnakeCase'))->convertTo(new SnakeCase());
        static::assertSame('complex_field_name_snake_case', $case->getString());
    }
}
