<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class TemplateYieldTest extends TestCase
{
    private Template $tpl;

    public function testLoadTemplateFile(): void
    {
        $this->tpl = new Template();
        $this->tpl->parseFile(__DIR__ . '/templateTests/repeater.tpl');

        $r = $this->tpl->createRepeater('r');
        $testString = '';
        for ($i = 0; $i < 9; ++$i) {
            $testString .= $i;
            $r->set('i', $i)->save();
        }

        $output = '';

        foreach ($this->tpl->yieldRun() as $tmp) {
            $output .= $tmp;
        }

        static::assertSame($output, '012345678');
    }
}
