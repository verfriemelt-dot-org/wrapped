<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class TemplateRepeaterTest extends TestCase
{
    public Template $tpl;

    public function setUp(): void
    {
        static::markTestSkipped('not implemented');
    }

    public function test_basic_repeater(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/repeater.tpl'));

        $r = $this->tpl->createRepeater('r');
        $testString = '';
        for ($i = 0; $i < 9; ++$i) {
            $testString .= $i;
            $r->set('i', $i)->save();
        }

        static::assertSame($testString, $this->tpl->run());
    }

    public function test_nested_repeater(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse((string) file_get_contents(__DIR__ . '/templateTests/nestedRepeater.tpl'));

        $k = $this->tpl->createRepeater('k');

        $testString = '';

        for ($ki = 0; $ki < 3; ++$ki) {
            $r = $k->createChildRepeater('r');

            for ($ri = 0; $ri < 3; ++$ri) {
                $testString .= $ki . '.' . $ri;
                $r->set('i', $ri)->save();
            }

            $k->set('j', $ki)->save();
        }

        static::assertSame($testString, $this->tpl->run());
    }
}
