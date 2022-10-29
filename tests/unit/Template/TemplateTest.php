<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class TemplateTest extends TestCase
{
    private Template $tpl;

    public function testLoadTemplateFile(): void
    {
        $this->tpl = new Template();
        $this->tpl->parseFile(__DIR__ . '/templateTests/testfile.tpl');

        static::assertSame($this->tpl->run(), '');
    }
}
