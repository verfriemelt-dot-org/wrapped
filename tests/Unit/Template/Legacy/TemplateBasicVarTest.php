<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\VariableFormatter;
use Override;

class TemplateBasicVarTest extends TestCase
{
    private Template $tpl;

    #[Override]
    public function setUp(): void
    {
        $formatter = new class implements VariableFormatter {
            #[Override]
            public function supports(string $name): bool
            {
                return true;
            }

            #[Override]
            public function format(string $input): string
            {
                return 'formatted';
            }
        };

        $container = new Container();
        $container->register($formatter::class, $formatter);
        $container->tag(VariableFormatter::class, $formatter::class);

        $this->tpl = $container->get(Template::class);
    }

    public function testsingle_var(): void
    {
        $this->tpl->parse('{{ var1 }}');
        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->render(), 'test');
    }

    public function testsingle_var_with_format(): void
    {
        $this->tpl->parse('{{ var1|format }}');
        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->render(), 'formatted');

        $this->tpl->parse('{{ var1 }}');
        static::assertSame($this->tpl->render(), 'test');
    }

    public function testsame_var_twice(): void
    {
        $this->tpl->parse('{{ var1 }} {{ var1 }}');

        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->render(), 'test test');
    }

    public function test_two_vars(): void
    {
        $this->tpl->parse('{{ var1 }} {{ var2 }}');

        $this->tpl->set('var1', 'test1');
        $this->tpl->set('var2', 'test2');

        static::assertSame($this->tpl->render(), 'test1 test2');
    }

    public function test_output_should_be_escaped(): void
    {
        $this->tpl->parse('{{ var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->render(), '&lt; &gt; &amp; &#039; &quot;');
    }

    public function test_output_can_be_unescaped(): void
    {
        $this->tpl->parse('{{ !var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->render(), "< > & ' \"");
    }

    public function test_empty_variables(): void
    {
        $this->tpl->parse('{{ }}');
        static::assertEmpty($this->tpl->render());
    }
}
