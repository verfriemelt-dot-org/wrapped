<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class TemplateIfElseTest extends TestCase
{
    private Template $tpl;

    private array $testCases = [
        [
            'name' => 'standard if else',
            'tpldata' => '{{ if=\'test\' }}true{{ else=\'test\' }}false{{ /if=\'test\'}}',
            'tests' => [
                ['set' => true, 'expected' => 'true'],
                ['set' => false, 'expected' => 'false'],
            ],
        ],
        [
            'name' => 'negated standard if else',
            'tpldata' => '{{ !if=\'test\' }}true{{ else=\'test\' }}false{{ /if=\'test\'}}',
            'tests' => [
                ['set' => false, 'expected' => 'true'],
                ['set' => true, 'expected' => 'false'],
            ],
        ],
        [
            'name' => 'standard if null',
            'tpldata' => '{{ if=\'test\' }}true{{ /if=\'test\' }}',
            'tests' => [
                ['set' => true, 'expected' => 'true'],
                ['set' => false, 'expected' => ''],
            ],
        ],
        [
            'name' => 'negated standard if null',
            'tpldata' => '{{ !if=\'test\' }}true{{ /if=\'test\' }}',
            'tests' => [
                ['set' => true, 'expected' => ''],
                ['set' => false, 'expected' => 'true'],
            ],
        ],
        [
            'name' => 'negated and standard if null',
            'tpldata' => '{{ if=\'test\' }}true{{ /if=\'test\' }}{{ !if=\'test\' }}true{{ /if=\'test\' }}',
            'tests' => [
                ['set' => true, 'expected' => 'true'],
            ],
        ],
        [
            'name' => 'negated and standard if null',
            'tpldata' => '{{ if=\'test\' }}false{{ else=\'test\'}}true{{ /if=\'test\' }}{{ !if=\'test\' }}true{{ /if=\'test\' }}',
            'tests' => [
                ['set' => false, 'expected' => 'truetrue'],
                ['set' => true, 'expected' => 'false'],
            ],
        ],
    ];

    public function test(): void
    {
        foreach ($this->testCases as $cases) {
            $this->tpl = new Template();
            $this->tpl->setRawTemplate($cases['tpldata']);

            foreach ($cases['tests'] as $case) {
                $this->tpl->setIf('test', $case['set']);
                static::assertSame($case['expected'], $this->tpl->run(), $cases['name']);
            }
        }
    }

    public function test_nested_empty(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        static::assertSame('', $this->tpl->run());
    }

    public function test_nested_set_a(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('a');

        static::assertSame('aa', $this->tpl->run());
    }

    public function test_nested_set_b(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('b');

        static::assertEmpty($this->tpl->run());
    }

    public function test_nested_set_ab(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate((string) file_get_contents(__DIR__ . '/templateTests/ifelseNested.tpl'));

        $this->tpl->setIf('a');
        $this->tpl->setIf('b');

        static::assertSame('aba', $this->tpl->run());
    }
}
