<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;

class TemplateTest extends TestCase
{
    public function test_load_template_file(): void
    {
        $tpl = new Template(new TemplateRenderer(new Container()));
        $tpl->parse('');
        $tpl->render();

        static::assertSame($tpl->render(), '');
    }
}
