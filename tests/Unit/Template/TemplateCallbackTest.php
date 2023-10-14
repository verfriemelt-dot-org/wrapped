<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class foo
{
    public function bar(): string
    {
        return 'epic';
    }
}

class TemplateCallbackTest extends TestCase
{
    private Template $tpl;

    public function test_clousure(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ testingVar }}');

        $this->tpl->set('testingVar', fn () => 'epic');

        static::assertSame($this->tpl->run(), 'epic');
    }

    public function test_should_not_call_functions(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ testingVar }}');

        $this->tpl->set('testingVar', 'system');

        static::assertSame($this->tpl->run(), 'system');
    }
}
