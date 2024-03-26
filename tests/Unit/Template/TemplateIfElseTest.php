<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use Iterator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;

class TemplateIfElseTest extends TestCase
{
    private Template $tpl;

    #[Override]
    public function setUp(): void
    {
        $this->tpl = new Template(new TemplateRenderer(new Container()));
    }

    /**
     * @return Iterator<string, mixed>
     */
    public static function data(): Iterator
    {
        yield 'negated and standard if null 2' => [
            'tpldata' => '{{ if=\'test\' }}false{{ else=\'test\'}}true{{ /if=\'test\' }}{{ !if=\'test\' }}true{{ /if=\'test\' }}',
            'tests' => [
                ['set' => false, 'expected' => 'truetrue'],
                ['set' => true, 'expected' => 'false'],
            ],
        ];
    }

    /**
     * @param array<array{set: bool, expected: string}> $tests
     */
    #[DataProvider('data')]
    public function test(string $tpldata, array $tests): void
    {
        $this->tpl->parse($tpldata);

        foreach ($tests as $case) {
            $this->tpl->setIf('test', $case['set']);
            static::assertSame($case['expected'], $this->tpl->render());
        }
    }

    public function test_simple(): void
    {
        $this->tpl->parse("{{ if='foo'}}foo{{ /if='foo'}}");
        $this->tpl->setIf('foo');

        static::assertSame('foo', $this->tpl->render());
    }

    public function test_simple_false(): void
    {
        $this->tpl->parse("{{ if='foo'}}foo{{ /if='foo'}}");
        $this->tpl->setIf('foo', false);

        static::assertSame('', $this->tpl->render());
    }

    public function test_simple_negated(): void
    {
        $this->tpl->parse("{{ !if='foo'}}foo{{ /if='foo'}}");
        $this->tpl->setIf('foo');

        static::assertSame('', $this->tpl->render());
    }

    public function test_if_else_true(): void
    {
        $this->tpl->parse("{{ if='foo'}}foo{{else='foo'}}bar{{ /if='foo'}}");
        $this->tpl->setIf('foo');

        static::assertSame('foo', $this->tpl->render());
    }

    public function test_if_else_false(): void
    {
        $this->tpl->parse("{{ if='foo'}}foo{{else='foo'}}bar{{ /if='foo'}}");
        $this->tpl->setIf('foo', false);

        static::assertSame('bar', $this->tpl->render());
    }

    public function test_if_else_negated(): void
    {
        $this->tpl->parse("{{ !if='foo'}}foo{{else='foo'}}bar{{ /if='foo'}}");
        $this->tpl->setIf('foo');

        static::assertSame('bar', $this->tpl->render());
    }

    public function test_nested_empty(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        static::assertSame('', $this->tpl->render());
    }

    public function test_nested_set_a(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('a');

        static::assertSame('aa', $this->tpl->render());
    }

    public function test_nested_set_b(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('b');

        static::assertEmpty($this->tpl->render());
    }

    public function test_nested_set_ab(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('a');
        $this->tpl->setIf('b');

        static::assertSame('aba', $this->tpl->render());
    }
}
