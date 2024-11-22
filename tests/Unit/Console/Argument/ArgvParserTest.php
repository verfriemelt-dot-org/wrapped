<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Console\Argument;

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
        (new ArgvParser())->parse([]);
    }

    public function test_with_single_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test'));
        $parser->parse(['foo']);

        $argument = $parser->getArgument('test');
        static::assertSame('foo', $argument->get());
    }

    public function test_with_single_missing_argument(): void
    {
        static::expectException(ArgumentMissingException::class);

        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test'));
        $parser->parse([]);
    }

    public function test_with_single_missing_optional_argument(): void
    {
        static::expectNotToPerformAssertions();
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test', Argument::OPTIONAL));
        $parser->parse([]);
    }

    public function test_with_single_present_optional_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('test', Argument::OPTIONAL));
        $parser->parse(['foo']);

        $argument = $parser->getArgument('test');
        static::assertSame('foo', $argument->get());
    }

    public function test_with_unexpected_argument(): void
    {
        static::expectException(ArgumentUnexpectedException::class);

        $parser = new ArgvParser();
        $parser->parse(['foo']);
    }

    public function test_with_option_present(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo'));
        $parser->parse(['--foo']);

        $option = $parser->getOption('foo');
        static::assertTrue($option->present());
    }

    public function test_with_option_missing(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo'));
        $parser->parse([]);

        $option = $parser->getOption('foo');
        static::assertFalse($option->present());
    }

    public function test_with_required_option_missing(): void
    {
        static::expectException(OptionMissingException::class);
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::REQUIRED));
        $parser->parse([]);
    }

    public function test_with_option_unexpected(): void
    {
        static::expectException(OptionUnexpectedException::class);
        $parser = new ArgvParser();
        $parser->parse(['--foo']);
    }

    public function test_with_option_requiring_value_missing_value(): void
    {
        static::expectException(OptionMissingValueException::class);
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse(['--foo']);
    }

    public function test_with_option_requiring_value_provided(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse(['--foo', 'bar']);

        $option = $parser->getOption('foo');
        static::assertTrue($option->present());
        static::assertSame('bar', $option->get());
    }

    public function test_with_option_requiring_value_missing_option(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', Option::EXPECTS_VALUE));
        $parser->parse([]);

        $option = $parser->getOption('foo');
        static::assertFalse($option->present());
    }

    public function test_options_with_shorthand(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', short: 'b'));

        $parser->parse(['-f', '-b']);

        static::assertTrue($parser->getOption('foo')->present());
        static::assertTrue($parser->getOption('bar')->present());
    }

    public function test_options_with_shorthand_combined(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', short: 'b'));

        $parser->parse(['-fb']);

        static::assertTrue($parser->getOption('foo')->present());
        static::assertTrue($parser->getOption('bar')->present());
    }

    public function test_options_with_shorthand_combined_with_value(): void
    {
        $parser = new ArgvParser();
        $parser->addOptions(new Option('foo', short: 'f'));
        $parser->addOptions(new Option('bar', Option::EXPECTS_VALUE, short: 'b'));

        $parser->parse(['-fb', 'nope']);

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

        $parser->parse(['-bf', 'nope']);
    }

    public function test_optional_arguments_cannot_follow_required(): void
    {
        static::expectException(ArgumentUnexpectedException::class);
        static::expectExceptionMessage('after optional arguments');

        $parser = new ArgvParser();
        $parser->addArguments(new Argument('foo', Argument::OPTIONAL));
        $parser->addArguments(new Argument('bar', Argument::REQUIRED));
    }

    public function test_variadic_argument_must_be_last(): void
    {
        static::expectException(ArgumentUnexpectedException::class);
        static::expectExceptionMessage('after variadic');
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('bar', Argument::VARIADIC | Argument::REQUIRED));
        $parser->addArguments(new Argument('foo'));
    }

    public function test_variadic_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('bar', Argument::VARIADIC));
        $parser->parse(['foo', 'bar', 'nope']);

        static::assertSame('foo bar nope', $parser->getArgument('bar')->get());
    }

    public function test_variadic_optional_argument(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('bar', Argument::VARIADIC));
        $parser->parse([]);

        static::assertSame(null, $parser->getArgument('bar')->get());
    }

    public function test_variadic_required_argument(): void
    {
        static::expectException(ArgumentMissingException::class);
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('bar', Argument::VARIADIC | Argument::REQUIRED));
        $parser->parse([]);
    }

    public function test_variadic_argument_after_other(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('foo'));
        $parser->addArguments(new Argument('bar', Argument::VARIADIC));
        $parser->parse(['foo', 'bar', 'nope']);

        static::assertSame('foo', $parser->getArgument('foo')->get());
        static::assertSame('bar nope', $parser->getArgument('bar')->get());
    }

    public function test_parsing_double_dash(): void
    {
        $parser = new ArgvParser();
        $parser->addArguments(new Argument('bar', Argument::VARIADIC));
        $parser->parse(['--', '-foo', 'bar', 'nope']);

        static::assertSame('-foo bar nope', $parser->getArgument('bar')->get());
    }
}
