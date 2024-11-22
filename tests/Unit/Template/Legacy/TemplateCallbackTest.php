<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use Override;

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

    #[Override]
    public function setUp(): void
    {
        $container = new Container();
        $this->tpl = $container->get(Template::class);
    }

    public function test_clousure(): void
    {
        $this->tpl->parse('{{ testingVar }}');
        $this->tpl->set('testingVar', fn () => 'epic');

        static::assertSame($this->tpl->render(), 'epic');
    }

    public function test_should_not_call_functions(): void
    {
        $this->tpl->parse('{{ testingVar }}');
        $this->tpl->set('testingVar', 'system');

        static::assertSame($this->tpl->render(), 'system');
    }
}
