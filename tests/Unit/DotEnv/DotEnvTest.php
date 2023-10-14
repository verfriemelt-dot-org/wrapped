<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\DotEnv;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DotEnv\DotEnv;

class DotEnvTest extends TestCase
{
    public function test_single_file(): void
    {
        $dotenv = new DotEnv();
        $dotenv->load(\TEST_ROOT . '/Fixtures/DotEnv/valid.ini');

        static::assertSame('localhost', $_ENV['TEST_DB_HOST']);
        static::assertSame('5432', $_ENV['TEST_DB_PORT']);
        static::assertSame('', $_ENV['TEST_DB_USER']);
        static::assertSame('extrasecret', $_ENV['TEST_DB_PASSWORD']);

        static::assertIsString($_ENV['_WRAPPED_HANDLED_VARS']);

        $handled = explode(',', $_ENV['_WRAPPED_HANDLED_VARS']);

        static::assertContains('TEST_DB_HOST', $handled);
        static::assertContains('TEST_DB_PORT', $handled);
        static::assertContains('TEST_DB_USER', $handled);
        static::assertContains('TEST_DB_PASSWORD', $handled);
    }

    public function test_overloading(): void
    {
        $dotenv = new DotEnv();
        $dotenv->load(
            \TEST_ROOT . '/Fixtures/DotEnv/valid.ini',
            \TEST_ROOT . '/Fixtures/DotEnv/valid_overloading.ini',
        );

        static::assertSame('10000', $_ENV['TEST_DB_PORT']);
    }

    public function test_prevent_existing_env_overloading(): void
    {
        putenv('EXISTING_ENV=1');

        $dotenv = new DotEnv();
        $dotenv->load(
            \TEST_ROOT . '/Fixtures/DotEnv/existing_overloading.ini',
        );

        static::assertSame('1', getenv('EXISTING_ENV'), 'existing env should not be overwritten');
        static::assertSame('1', $_ENV['EXISTING_ENV'], 'copy over values to $_ENV');
    }
}
