<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Textarea extends Text
{
    protected string $type = 'textarea';

    #[Override]
    protected function loadTemplate(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/Template/Textarea.tpl.php');
    }

    #[Override]
    public function placeholder(string $placeholder): Textarea
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
