<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class TemplateTest extends TestCase
{
    private Template $tpl;

    public function test_load_template_file(): void
    {
        $this->tpl = new Template(__DIR__ . '/templateTests/testfile.tpl');
        $this->tpl->render();

        static::assertSame($this->tpl->render(), '');
    }
}
