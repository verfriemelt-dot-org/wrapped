<?php

declare(strict_types=1);

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\Template\Template;

class FormularTemplateTest extends TestCase
{
    /**
     * @return iterable<string,array{string}>
     */
    public static function templates(): iterable
    {
        $files = glob(dirname(__FILE__, 4) . '/_/Formular/Template/*.php');
        if ($files === false) {
            static::fail('cant glob templates');
        }

        foreach ($files as $filePath) {
            yield basename($filePath) => [$filePath];
        }
    }

    #[DataProvider('templates')]
    public function test(string $filepath): void
    {
        static::expectNotToPerformAssertions();

        $template = new Template();
        $template->parseFile($filepath);
        $template->setIf('pattern');
        $template->run();
    }
}
