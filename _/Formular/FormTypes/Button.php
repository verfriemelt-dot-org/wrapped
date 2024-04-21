<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Button extends FormType
{
    protected string $type = 'button';

    #[Override]
    protected function loadTemplate(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/Template/Button.tpl.php');
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    #[Override]
    public function render(): string
    {
        $this->addCssClass('btn btn-default');

        $this->writeTplValues();

        return $this->template->render();
    }
}
