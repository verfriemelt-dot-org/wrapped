<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\Variable;

class TemplateBasicVarTest extends TestCase
{
    private Template $tpl;

    public function testsingle_var(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1 }}');
        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->render(), 'test');
    }

    public function testsingle_var_with_format(): void
    {
        static::markTestSkipped('not implemented yet');
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1|test}}');

        $this->tpl->set('var1', 'test');

        Variable::registerFormat('test', fn (string $input): string => 'formatted');

        static::assertSame($this->tpl->render(), 'formatted');

        $this->tpl->parse('{{ var1 }}');
        static::assertSame($this->tpl->render(), 'test');
    }

    public function testsame_var_twice(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1 }} {{ var1 }}');

        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->render(), 'test test');
    }

    public function test_two_vars(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1 }} {{ var2 }}');

        $this->tpl->set('var1', 'test1');
        $this->tpl->set('var2', 'test2');

        static::assertSame($this->tpl->render(), 'test1 test2');
    }

    public function test_two_vars_with_set_array(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1 }} {{ var2 }}');

        $this->tpl->setArray(['var1' => 'test1', 'var2' => 'test2']);

        static::assertSame($this->tpl->render(), 'test1 test2');
    }

    public function test_set_array_should_only_work_with_arrays(): void
    {
        $this->tpl = new Template();
        static::assertSame($this->tpl->setArray(false), false);
    }

    public function test_output_should_be_escaped(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->render(), '&lt; &gt; &amp; &#039; &quot;');
    }

    public function test_output_can_be_unescaped(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ !var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->render(), "< > & ' \"");
    }

    public function test_empty_variables(): void
    {
        $this->tpl = new Template();
        $this->tpl->parse('{{ }}');
        static::assertEmpty($this->tpl->render());
    }
}
