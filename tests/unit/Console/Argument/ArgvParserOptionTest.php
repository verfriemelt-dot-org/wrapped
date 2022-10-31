<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\unit\Console\Argument;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Cli\Argument\ArgvParser;
use verfriemelt\wrapped\_\Cli\Argument\Option;
use verfriemelt\wrapped\_\Cli\Argument\OptionMissingValueException;

class ArgvParserOptionTest extends TestCase
{
    public function testOptionHasValue(): void
    {
        $argument = new ArgvParser(['script.php', '-a', 'test']);
        $argument->addOptions(new Option('abc', flags: Option::EXPECTS_VALUE, short: 'a'));

        $argument->parse();
        $option = $argument->getOption('abc');

        static::assertTrue($option->isPresent());
        static::assertSame('test', $option->getValue());
    }

    public function testOptionValueMissingException(): void
    {
        static::expectException(OptionMissingValueException::class);

        $argument = new ArgvParser(['script.php', '-a']);
        $argument->addOptions(new Option('abc', flags: Option::EXPECTS_VALUE, short: 'a'));

        $argument->parse();
    }

    public function testPresentOnNoValue(): void
    {
        $argument = new ArgvParser(['script.php', '-a']);
        $argument->addOptions(new Option('abc', short: 'a'));

        $argument->parse();
        static::assertTrue($argument->getOption('abc')->isPresent());
    }

    public function testCombinedOptions(): void
    {
        $argument = new ArgvParser(['script.php', '-abc']);
        $argument->addOptions(new Option('test1', short: 'a'));
        $argument->addOptions(new Option('test2', short: 'b'));
        $argument->addOptions(new Option('test3', short: 'c'));

        $argument->parse();

        static::assertTrue($argument->getOption('test1')->isPresent());
        static::assertTrue($argument->getOption('test2')->isPresent());
        static::assertTrue($argument->getOption('test3')->isPresent());
    }

    public function testSplitCombinedOptions(): void
    {
        $argument = new ArgvParser(['script.php', '-ab', '-c']);
        $argument->addOptions(new Option('test1', short: 'a'));
        $argument->addOptions(new Option('test2', short: 'b'));
        $argument->addOptions(new Option('test3', short: 'c'));

        $argument->parse();

        static::assertTrue($argument->getOption('test3')->isPresent(), 'c must be present');
        static::assertTrue($argument->getOption('test2')->isPresent(), 'b must be present');
        static::assertTrue($argument->getOption('test1')->isPresent(), 'a must be present');
    }

    public function testCombinedWithValue(): void
    {
        $argument = new ArgvParser(['script.php', '-ab', 'test']);
        $argument->addOptions(new Option('test1', short: 'a'));
        $argument->addOptions(new Option('test2', short: 'b', flags: Option::EXPECTS_VALUE));

        $argument->parse();

        static::assertTrue($argument->getOption('test1')->isPresent(), 'a must be present');
        static::assertTrue($argument->getOption('test2')->isPresent(), 'b must be present');
        static::assertSame('test', $argument->getOption('test2')->getValue());
    }

    public function testCombinedWithValueWrongOrder(): void
    {
        static::expectException(\RuntimeException::class);

        $argument = new ArgvParser(['script.php', '-ba', 'test']);
        $argument->addOptions(new Option('test1', short: 'a'));
        $argument->addOptions(new Option('test2', short: 'b', flags: Option::EXPECTS_VALUE));

        $argument->parse();
    }
}
