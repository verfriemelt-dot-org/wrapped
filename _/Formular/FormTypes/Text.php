<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Text extends FormType
{
    public $type = 'text';

    public $placeholder;

    #[Override]
    public function loadTemplate(): FormType
    {
        $this->tpl->parseFile(dirname(__DIR__) . '/Template/Text.tpl.php');
        return $this;
    }

    public function placeholder($placeholder): Text
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    #[Override]
    public function fetchHtml(): string
    {
        $this->writeTplValues();

        if ($this->placeholder) {
            $this->tpl->setIf('placeholder');
            $this->tpl->set('placeholder', $this->placeholder);
        }

        return $this->tpl->run();
    }
}
