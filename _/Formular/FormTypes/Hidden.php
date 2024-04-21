<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Hidden extends FormType
{
    protected string $type = 'hidden';

    #[Override]
    protected function loadTemplate(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/Template/Hidden.tpl.php');
    }

    #[Override]
    public function render(): string
    {
        $this->template->parse($this->loadTemplate());

        $this->template->set('value', $this->value);
        $this->template->set('name', $this->name);
        $this->template->set('id', $this->name);

        $this->template->set('cssClasses', implode(' ', $this->cssClasses));

        return $this->template->render();
    }
}
