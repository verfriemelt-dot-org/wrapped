<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\unit\Console\Argument;

use Generator;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Cli\Argument\ArgvParser;

class ArgvParserTest extends TestCase
{
    public function testEmpty(): void
    {
        static::expectException(\RuntimeException::class);
        new ArgvParser([]);
    }

    public function testAssocArray(): void
    {
        static::expectException(\RuntimeException::class);
        new ArgvParser(['foo' => 'bar']);
    }

    public function testgetSciptName(): void
    {
        $argument = new ArgvParser(['script.php']);
        static::assertSame('script.php', $argument->getScript());
    }

    /**
     * @return Generator<string, array{input: string[], expected: string[], definition?: string[]}>
     */
    protected function arguments(): Generator
    {
        yield 'empty' => [
            'input' => ['script.php'],
            'expected' => [],
        ];

        yield 'simple' => [
            'input' => ['script.php', 'a', 'b'],
            'expected' => ['a', 'b'],
        ];

        yield 'repeated' => [
            'input' => ['script.php', 'a', 'a'],
            'expected' => ['a', 'a'],
        ];

        yield 'mixed with short' => [
            'input' => ['script.php', '-a', 'a', 'b'],
            'expected' => ['a', 'b'],
        ];

        yield 'mixed with long' => [
            'input' => ['script.php', '-a', 'a', '--logn', 'b'],
            'expected' => ['a', 'b'],
        ];
    }

    /**
     * @dataProvider arguments
     *
     * @param string[] $input
     * @param string[] $expected
     * @param string[] $definitions
     */
    public function testGetArguements(array $input, array $expected, array $definitions = []): void
    {
        $argument = new ArgvParser($input);
        static::assertSame($expected, $argument->getArguments());
    }

    public function testGetShortOptions(): void
    {
        $argument = new ArgvParser(['script.php', '-a']);
        static::assertSame(['-a'], $argument->getShortOptions());

        $argument = new ArgvParser(['script.php']);
        static::assertSame([], $argument->getShortOptions());
    }
}
