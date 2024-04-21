<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Text extends FormType
{
    protected string $type = 'text';
    protected string $placeholder;

    #[Override]
    public function loadTemplate(): static
    {
        $this->tpl->parse(\file_get_contents(\dirname(__DIR__) . '/Template/Text.tpl.php'));
        return $this;
    }

    public function placeholder(string $placeholder): Text
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    #[Override]
    public function fetchHtml(): string
    {
        $this->writeTplValues();

        if (isset($this->placeholder)) {
            $this->tpl->setIf('placeholder');
            $this->tpl->set('placeholder', $this->placeholder);
        }

        return $this->tpl->render();
    }
}
