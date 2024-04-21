<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Textarea extends Text
{
    public $type = 'textarea';

    public $placeholder;

    #[Override]
    public function loadTemplate(): static
    {
        $this->tpl->parse(\file_get_contents(\dirname(__DIR__) . '/Template/Textarea.tpl.php'));
        return $this;
    }

    #[Override]
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

        return $this->tpl->render();
    }
}
