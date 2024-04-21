<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Checkbox extends FormType
{
    protected string $type = 'checkbox';

    private bool $checked = false;

    #[Override]
    protected function loadTemplate(): string
    {
        return \file_get_contents(\dirname(__DIR__) . '/Template/Checkbox.tpl.php');
    }

    #[Override]
    public function render(): string
    {
        $this->writeTplValues();
        return $this->template->render();
    }

    public function checked(bool $bool = true): static
    {
        $this->checked = $bool;
        return $this;
    }

    #[Override]
    protected function writeTplValues(): static
    {
        parent::writeTplValues();
        $this->template->setIf('checked', $this->checked);

        return $this;
    }
}
