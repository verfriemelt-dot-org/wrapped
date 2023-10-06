<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

class Button extends FormType
{
    public $type = 'button';

    public function __construct(
        string $name,
        ?string $value = null,
        ?\verfriemelt\wrapped\_\Template\Template $template = null
    ) {
        parent::__construct($name, $value, $template);

        $this->addCssClass('btn btn-default');
    }

    public function loadTemplate(): FormType
    {
        $this->tpl->parseFile(dirname(__DIR__) . '/Template/Button.tpl.php');
        return $this;
    }

    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    public function fetchHtml(): string
    {
        $this->writeTplValues();

        return $this->tpl->run();
    }
}
