<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use stdClass;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Processor\TemplateProcessorException;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\VariableFormatter;
use Override;

class VariableDotNotationTest extends TestCase
{
    private Template $tpl;
    private Container $container;

    #[Override]
    public function setUp(): void
    {
        $this->container = new Container();
        $this->tpl = $this->container->get(Template::class);
    }

    public function test_read_scalar(): void
    {
        $this->tpl->parse('{{ foo }}');
        $result = $this->tpl->render([
            'foo' => 'test',
        ]);

        static::assertSame('test', $result);
    }

    public function test_read_from_array(): void
    {
        $this->tpl->parse('{{ foo.bar }}');
        $result = $this->tpl->render([
            'foo' => ['bar' => 'test'],
        ]);

        static::assertSame('test', $result);
    }

    public function test_read_from_object(): void
    {
        $class = new stdClass();
        $class->bar = 'test';

        $this->tpl->parse('{{ foo.bar }}');
        $result = $this->tpl->render([
            'foo' => $class,
        ]);

        static::assertSame('test', $result);
    }

    public function test_read_from_method(): void
    {
        $class = new class {
            public function bar(): string
            {
                return 'test';
            }
        };

        $this->tpl->parse('{{ foo.bar() }}');
        $result = $this->tpl->render([
            'foo' => $class,
        ]);

        static::assertSame('test', $result);
    }

    public function test_read_from_callable(): void
    {
        $this->tpl->parse('{{ foo }}');
        $result = $this->tpl->render([
            'foo' => static fn (): string => 'test',
        ]);

        static::assertSame('test', $result);
    }

    public function test_read_from_callable_with_formatter(): void
    {
        $formatter = new class implements VariableFormatter {
            public function supports(string $name): bool
            {
                return true;
            }

            public function format(string $input): string
            {
                return 'hi ' . $input;
            }
        };

        $this->container->register($formatter::class, $formatter);
        $this->container->tag(VariableFormatter::class, $formatter::class);

        $this->tpl->parse('{{ foo|formatter }}');
        $result = $this->tpl->render([
            'foo' => static fn (): string => 'test',
        ]);

        static::assertSame('hi test', $result);
    }

    public function test_read_scalar_unescaped(): void
    {
        $this->tpl->parse('{{ !foo }}');
        $result = $this->tpl->render([
            'foo' => '<b>hi</b>',
        ]);

        static::assertSame('<b>hi</b>', $result);
    }

    public function test_read_scalar_escaped(): void
    {
        $this->tpl->parse('{{ foo }}');
        $result = $this->tpl->render([
            'foo' => '<b>hi</b>',
        ]);

        static::assertSame('&lt;b&gt;hi&lt;/b&gt;', $result);
    }

    public function test_non_scalar(): void
    {
        static::expectException(TemplateProcessorException::class);

        $this->tpl->parse('{{ foo }}');
        $this->tpl->render([
            'foo' => [],
        ]);
    }
}
