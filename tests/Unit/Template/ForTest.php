<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;
use verfriemelt\wrapped\_\Template\Token\EndForToken;
use verfriemelt\wrapped\_\Template\Token\ForToken;
use verfriemelt\wrapped\_\Template\Token\VariableToken;
use verfriemelt\wrapped\_\Template\Tokenizer;
use Override;

class ForTest extends TestCase
{
    private Template $tpl;
    private Container $container;

    #[Override]
    public function setUp(): void
    {
        $this->container = new Container();
        $this->tpl = new Template(new TemplateRenderer($this->container));
    }

    public function test_tokenizer(): void
    {
        $tokenizer = new Tokenizer();
        $root = $tokenizer->parse('{{ for array as item }}{{ item }}{{ endfor }}');

        static::assertCount(1, $root->children());

        $forToken = $root->children()[0];

        static::assertInstanceOf(ForToken::class, $forToken);
        static::assertCount(2, $forToken->children());
        static::assertInstanceOf(VariableToken::class, $forToken->children()[0]);
        static::assertInstanceOf(EndForToken::class, $forToken->children()[1]);
    }

    public function test_empty_loop(): void
    {
        $this->tpl->parse('{{ for array as item }}{{ item }}{{ endfor }}');
        $result = $this->tpl->render([
            'array' => range(1, 5),
        ]);

        static::assertSame('12345', $result);
    }

    public function test_nested_loop(): void
    {
        $this->tpl->parse(
            <<<TPL
            {{ for a as item-a }}{{ for b as item-b }}{{ item-a }}:{{ item-b }}{{ endfor }}{{ endfor }}
            TPL
        );
        $result = $this->tpl->render([
            'a' => range(1, 3),
            'b' => range(1, 2),
        ]);

        static::assertSame(
            '1:11:22:12:23:13:2',
            $result
        );
    }
}
