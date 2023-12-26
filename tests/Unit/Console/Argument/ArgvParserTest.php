<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Console\Argument;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgumentMissingException;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgumentUnexpectedException;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgvParser;
use verfriemelt\wrapped\_\Command\CommandArguments\Option;
use verfriemelt\wrapped\_\Command\CommandArguments\OptionMissingException;
use verfriemelt\wrapped\_\Command\CommandArguments\OptionMissingValueException;
use verfriemelt\wrapped\_\Command\CommandArguments\OptionUnexpectedException;

class ArgvParserTest extends TestCase
{
    public function test_new_instance(): void
    {
        static::expectNotToPerformAssertions();
        (new ArgvParser())->parse(['script.php']);
    }

    public function test_with_single_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test'));
        $parser->parse(['script.php', 'foo']);

        $argument = $parser->getArgument('test');
        static::assertSame('foo', $argument->get());
    }

    public function test_with_single_missing_argument(): void
    {
        static::expectException(ArgumentMissingException::class);

        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test'));
        $parser->parse(['script.php']);
    }

    public function test_with_single_missing_optional_argument(): void
    {
        static::expectNotToPerformAssertions();
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test', Argument::OPTIONAL));
        $parser->parse(['script.php']);
    }

    public function test_with_single_present_optional_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test', Argument::OPTIONAL));
        $parser->parse(['script.php', 'foo']);

        $argument = $parser->getArgument('test');
        static::assertSame('foo', $argument->get());
    }

    public function test_with_unexpected_argument(): void
    {
        static::expectException(ArgumentUnexpectedException::class);

        $parser = new ArgvParser();
        $parser->parse(['script.php', 'foo']);
    }

    public function test_with_option_present(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo'));
        $parser->parse(['script.php', '--foo']);

        $option = $parser->getOption('foo');
        static::assertTrue($option->present());
    }

    public function test_with_option_missing(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo'));
        $parser->parse(['script.php']);

        $option = $parser->getOption('foo');
        static::assertFalse($option->present());
    }

    public function test_with_required_option_missing(): void
    {
        static::expectException(OptionMissingException::class);
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::REQUIRED));
        $parser->parse(['script.php']);
    }

    public function test_with_option_unexpected(): void
    {
        static::expectException(OptionUnexpectedException::class);
        $parser = new ArgvParser();
        $parser->parse(['script.php', '--foo']);
    }

    public function test_with_option_requiring_value_missing_value(): void
    {
        static::expectException(OptionMissingValueException::class);
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse(['script.php', '--foo']);
    }

    public function test_with_option_requiring_value_provided(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse(['script.php', '--foo', 'bar']);

        $option = $parser->getOption('foo');
        static::assertTrue($option->present());
        static::assertSame('bar', $option->get());
    }

    public function test_with_option_requiring_value_missing_option(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse(['script.php']);

        $option = $parser->getOption('foo');
        static::assertFalse($option->present());
    }

    public function test_options_with_shorthand(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', short: 'b'));

        $parser->parse(['script.php', '-f', '-b']);

        static::assertTrue($parser->getOption('foo')->present());
        static::assertTrue($parser->getOption('bar')->present());
    }

    public function test_options_with_shorthand_combined(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', short: 'b'));

        $parser->parse(['script.php', '-fb']);

        static::assertTrue($parser->getOption('foo')->present());
        static::assertTrue($parser->getOption('bar')->present());
    }

    public function test_options_with_shorthand_combined_with_value(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', Option::EXPECTS_VALUE, short: 'b'));

        $parser->parse(['script.php', '-fb', 'nope']);

        static::assertTrue($parser->getOption('foo')->present());
        static::assertTrue($parser->getOption('bar')->present());
        static::assertSame('nope', $parser->getOption('bar')->get());
    }

    public function test_options_with_shorthand_combined_with_value_mixed_up(): void
    {
        static::expectException(OptionMissingValueException::class);

        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', Option::EXPECTS_VALUE, short: 'b'));

        $parser->parse(['script.php', '-bf', 'nope']);
    }
}
