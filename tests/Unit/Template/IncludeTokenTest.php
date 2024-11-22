<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Template;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Kernel\AbstractKernel;
use verfriemelt\wrapped\_\Kernel\KernelInterface;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\Token\IncludeToken;
use verfriemelt\wrapped\_\Template\Tokenizer;
use Override;

class IncludeTokenTest extends TestCase
{
    private Template $tpl;
    private KernelInterface $kernel;

    #[Override]
    public function setUp(): void
    {
        $this->kernel = new class extends AbstractKernel {
            public function getProjectPath(): string
            {
                return __DIR__;
            }
        };

        $this->tpl = $this->kernel->getContainer()->get(Template::class);
    }

    #[Override]
    public function tearDown(): void
    {
        $this->kernel->shutdown();
    }

    public function test_tokenizer(): void
    {
        $tokenizer = new Tokenizer();
        $root = $tokenizer->parse('{{ include templateTests/include.tpl }}');

        static::assertCount(1, $root->children());

        $includeToken = $root->children()[0];

        static::assertInstanceOf(IncludeToken::class, $includeToken);
        static::assertSame('templateTests/include.tpl', $includeToken->getPath());
    }

    public function test_parse_template(): void
    {
        $out = $this->tpl->parse('{{ include templateTests/include.tpl }}')->render(['location' => 'world']);

        static::assertSame(
            <<<OUT
            hello world
            
            OUT,
            $out,
        );
    }
}
