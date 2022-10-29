<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\unit\Console;

use Generator;
use PHPUnit\Framework\TestCase;

use function rewind;

use verfriemelt\wrapped\_\Cli\KeyInput;

class KeyInputTest extends TestCase
{
    protected function keys(): Generator
    {
        yield 'simple key' => [
            'key' => 'a',
            'input' => 'a',
        ];

        yield 'enter' => [
            'key' => 'enter',
            'input' => chr(13),
        ];

        yield 'F1' => [
            'key' => 'F1',
            'input' => implode('', array_map(fn (int $i): string => chr($i), [27, 79, 80])),
        ];

        yield 'F5' => [
            'key' => 'F5',
            'input' => implode('', array_map(fn (int $i): string => chr($i), [27, 91, 49, 53, 126])),
        ];
    }

    /**
     * @dataProvider keys
     */
    public function testKeyWasRead(string $key, string $input): void
    {
        $keyInput = new KeyInput();
        $stream = fopen('php://temp', 'rw');

        static::assertNotFalse($stream);

        fwrite($stream, $input);
        rewind($stream);

        $keyWasRead = false;

        $keyInput->registerKey($key, function () use (&$keyWasRead): void {
            $keyWasRead = true;
        });

        $keyInput->consume($stream);

        static::assertTrue($keyWasRead);
    }
}
