<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Database\SQL\Command;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Database\SQL\Command\Delete;
use verfriemelt\wrapped\_\Database\SQL\Expression\Identifier;

class DeleteTest extends TestCase
{
    public function test_simple(): void
    {
        $delete = new Delete(new Identifier('table'));
        static::assertSame('DELETE FROM table', $delete->stringify());
    }
}
