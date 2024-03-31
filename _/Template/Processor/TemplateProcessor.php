<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Processor;

use verfriemelt\wrapped\_\Template\Token\Token;

interface TemplateProcessor
{
    /**
     * @param mixed[] $data
     */
    public function process(Token $token, array $data): string;
}
