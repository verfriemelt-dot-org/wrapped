<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\unit\Console\Argument;

use Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;
use verfriemelt\wrapped\_\Cli\Argument\Argument;
use verfriemelt\wrapped\_\Cli\Argument\ArgumentDuplicatedException;
use verfriemelt\wrapped\_\Cli\Argument\ArgumentMissingException;
use verfriemelt\wrapped\_\Cli\Argument\ArgvParser;

class ArgvParserTest extends TestCase
{
    public function testEmpty(): void
    {
        static::expectException(RuntimeException::class);
        (new ArgvParser([]))->parse();
    }

    public function testAssocArray(): void
    {
        static::expectException(RuntimeException::class);
        (new ArgvParser(['foo' => 'bar']))->parse();
    }

    public function testgetSciptName(): void
    {
        $argument = new ArgvParser(['script.php']);
        static::assertSame('script.php', $argument->getScript());
    }

    /**
     * @return Generator<string, array{input: string[], expected: Throwable|string[], args?: Argument[]}>
     */
    protected function arguments(): Generator
    {
        yield 'no input' => [
            'input' => [],
            'expected' => new RuntimeException(),
        ];

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

        yield 'described argument' => [
            'input' => ['script', 'a'],
            'expected' => ['a'],
            'args' => [new Argument('test')],
        ];

        yield 'missing argument' => [
            'input' => ['script'],
            'expected' => new ArgumentMissingException(),
            'args' => [new Argument('test')],
        ];

        yield 'sencond argument missing after' => [
            'input' => ['script', 'a'],
            'expected' => new ArgumentMissingException(),
            'args' => [new Argument('test'), new Argument('test2')],
        ];

        yield 'missing optional argument' => [
            'input' => ['script'],
            'expected' => [],
            'args' => [new Argument('test', true)],
        ];

        yield 'mixed arguments missing' => [
            'input' => ['script', 'a'],
            'expected' => ['a'],
            'args' => [new Argument('test'), new Argument('test2', true)],
        ];

        yield 'argument already present' => [
            'input' => ['script', 'a', 'b'],
            'expected' => new ArgumentDuplicatedException(),
            'args' => [new Argument('test'), new Argument('test')],
        ];

        yield 'optional argument first' => [
            'input' => ['script', 'a', 'b'],
            'expected' => ['a', 'b'],
            'args' => [new Argument('test', true), new Argument('test2')],
        ];

        yield 'optional argument first, missing second required' => [
            'input' => ['script', 'a'],
            'expected' => new ArgumentMissingException(),
            'args' => [new Argument('test', true), new Argument('test2')],
        ];
    }

    /**
     * @dataProvider arguments
     *
     * @param string[]           $input
     * @param Throwable|string[] $expected
     * @param Argument[]         $arguments
     */
    public function testGetArguements(array $input, array|Throwable $expected, array $arguments = []): void
    {
        if ($expected instanceof Throwable) {
            static::expectException($expected::class);
        }

        $argument = new ArgvParser($input);
        $argument->addArguments(...$arguments);
        $argument->parse();

        if (!$expected instanceof Throwable) {
            static::assertSame($expected, $argument->getRawArguments());
        }
    }

    public function testGetShortOptions(): void
    {
        $argument = new ArgvParser(['script.php', '-a']);
        $argument->parse();
        static::assertSame(['-a'], $argument->getShortOptions());

        $argument = new ArgvParser(['script.php']);
        $argument->parse();
        static::assertSame([], $argument->getShortOptions());
    }

    public function testParserWorksTwice(): void
    {
        $parser = new ArgvParser(['script.php', 'a', 'b']);
        $parser->addArguments(new Argument('test-1'));
        $parser->parse();

        $parser->addArguments(new Argument('test-2'));
        $parser->parse();

        static::assertSame('a', $parser->getArgument('test-1')->getValue());
        static::assertSame('b', $parser->getArgument('test-2')->getValue());
    }
}
