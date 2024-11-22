<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Console\Argument;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Command\CommandArguments\Argument;
use verfriemelt\wrapped\_\Command\CommandArguments\ArgumentDefinitionError;

class ArgumentTest extends TestCase
{
    public function test_required_with_default(): void
    {
        static::expectException(ArgumentDefinitionError::class);
        new Argument(
            'foo',
            Argument::REQUIRED,
            default: 'bar',
        );
    }
}
