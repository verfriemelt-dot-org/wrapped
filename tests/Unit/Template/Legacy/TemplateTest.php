<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;

class TemplateTest extends TestCase
{
    public function test_load_template_file(): void
    {
        $container = new Container();
        $tpl = $container->get(Template::class);
        $tpl->parse('');
        $tpl->render();

        static::assertSame($tpl->render(), '');
    }
}
