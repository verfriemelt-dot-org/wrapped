<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Formular;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use Override;

class FormularTemplateTest extends TestCase
{
    #[Override]
    public function setUp(): void {}

    /**
     * @return iterable<string,array{string, callable(Template): void|null}>
     */
    public static function templates(): iterable
    {
        $files = glob(dirname(__FILE__, 4) . '/_/Formular/Template/*.php');
        if ($files === false) {
            static::fail('cant glob templates');
        }

        $setups = [
            // we need nested ifs set to true and repeater
            'Select.tpl.php' => function (Template $t): void {
                $r = $t->createRepeater('options');
                $r->setIf('option');
                $r->save();
            },
        ];

        foreach ($files as $filePath) {
            $basename = basename($filePath);
            yield $basename => [$filePath, $setups[$basename] ?? null];
        }
    }

    /**
     * @param callable(Template): void|null $setup
     */
    #[DataProvider('templates')]
    public function test(string $filepath, ?callable $setup = null): void
    {
        static::expectNotToPerformAssertions();

        $template = (new Container())->get(Template::class);
        $template->parse($filepath);
        $template->render();

        if ($setup !== null) {
            $setup($template);
        }

        $template->render();
    }
}
