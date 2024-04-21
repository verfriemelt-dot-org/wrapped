<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;
use RuntimeException;

class Text extends FormType
{
    protected string $type = 'text';
    protected string $placeholder;

    #[Override]
    protected function loadTemplate(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/Template/Text.tpl.php') ?: throw new RuntimeException('cant load template');
    }

    public function placeholder(string $placeholder): Text
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    #[Override]
    public function render(): string
    {
        $this->writeTplValues();

        if (isset($this->placeholder)) {
            $this->template->setIf('placeholder');
            $this->template->set('placeholder', $this->placeholder);
        }

        return $this->template->render();
    }
}
