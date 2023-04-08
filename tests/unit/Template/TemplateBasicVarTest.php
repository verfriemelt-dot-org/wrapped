<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\Variable;

class TemplateBasicVarTest extends TestCase
{
    private Template $tpl;

    public function testsingleVar(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1 }}');

        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->run(), 'test');
    }

    public function testsingleVarWithFormat(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1|test}}');

        $this->tpl->set('var1', 'test');

        Variable::registerFormat('test', fn ($input) => 'formatted');

        static::assertSame($this->tpl->run(), 'formatted');

        $this->tpl->setRawTemplate('{{ var1 }}');
        static::assertSame($this->tpl->run(), 'test');
    }

    public function testsameVarTwice(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1 }} {{ var1 }}');

        $this->tpl->set('var1', 'test');

        static::assertSame($this->tpl->run(), 'test test');
    }

    public function testTwoVars(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1 }} {{ var2 }}');

        $this->tpl->set('var1', 'test1');
        $this->tpl->set('var2', 'test2');

        static::assertSame($this->tpl->run(), 'test1 test2');
    }

    public function testTwoVarsWithSetArray(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1 }} {{ var2 }}');

        $this->tpl->setArray(['var1' => 'test1', 'var2' => 'test2']);

        static::assertSame($this->tpl->run(), 'test1 test2');
    }

    public function testSetArrayShouldOnlyWorkWithArrays(): void
    {
        $this->tpl = new Template();
        static::assertSame($this->tpl->setArray(false), false);
    }

    public function testOutputShouldBeEscaped(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->run(), '&lt; &gt; &amp; &#039; &quot;');
    }

    public function testOutputCanBeUnescaped(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ !var1 }}');

        $this->tpl->set('var1', "< > & ' \"");

        static::assertSame($this->tpl->run(), "< > & ' \"");
    }

    public function testEmptyVariables(): void
    {
        $this->tpl = new Template();
        $this->tpl->setRawTemplate('{{ }}');
        static::assertEmpty($this->tpl->run());
    }
}
