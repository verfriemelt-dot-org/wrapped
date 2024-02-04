<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;

class Checkbox extends FormType
{
    public $type = 'checkbox';

    private $checked;

    public function __construct(
        string $name,
        ?string $value = null,
        ?\verfriemelt\wrapped\_\Template\Template $template = null
    ) {
        parent::__construct($name, $value, $template);
    }

    #[Override]
    public function loadTemplate(): FormType
    {
        $this->tpl->parseFile(dirname(__DIR__) . '/Template/Checkbox.tpl.php');
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    #[Override]
    public function fetchHtml(): string
    {
        $this->writeTplValues();

        return $this->tpl->run();
    }

    public function checked($bool = true): FormType
    {
        $this->checked = $bool;
        return $this;
    }

    #[Override]
    protected function writeTplValues(): FormType
    {
        parent::writeTplValues();
        $this->tpl->setIf('checked', $this->checked);

        return $this;
    }
}
