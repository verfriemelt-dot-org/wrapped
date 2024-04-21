<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use Override;

class TemplateRepeaterTest extends TestCase
{
    public Template $tpl;

    #[Override]
    public function setUp(): void
    {
        $container = new Container();
        $this->tpl = $container->get(Template::class);
    }

    public function test_basic_repeater(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/../templateTests/repeater.tpl'));

        $r = $this->tpl->createRepeater('r');
        $testString = '';
        for ($i = 0; $i < 9; ++$i) {
            $testString .= $i;
            $r->set('i', (string) $i)->save();
        }

        static::assertSame($testString, $this->tpl->render());
    }

    public function test_nested_repeater(): void
    {
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/../templateTests/nestedRepeater.tpl'));

        $k = $this->tpl->createRepeater('k');

        $testString = '0.00.10.21.01.11.22.02.12.2';

        for ($ki = 0; $ki < 3; ++$ki) {
            $r = $k->createChildRepeater('r');

            for ($ri = 0; $ri < 3; ++$ri) {
                $r->set('i', (string) $ri)->save();
            }

            $k->set('j', (string) $ki)->save();
        }

        static::assertSame($testString, $this->tpl->render());
    }
}
