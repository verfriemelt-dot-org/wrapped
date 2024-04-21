<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use Override;
use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;

class Checkbox extends FormType
{
    public $type = 'checkbox';

    private $checked;

    public function __construct(
        string $name,
        ?string $value = null,
        Template $template = new Template(new TemplateRenderer(new Container())),
    ) {
        parent::__construct($name, $value, $template);
    }

    #[Override]
    public function loadTemplate(): static
    {
        $this->tpl->parse(\file_get_contents(\dirname(__DIR__) . '/Template/Checkbox.tpl.php'));
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

        return $this->tpl->render();
    }

    public function checked($bool = true): static
    {
        $this->checked = $bool;
        return $this;
    }

    #[Override]
    protected function writeTplValues(): static
    {
        parent::writeTplValues();
        $this->tpl->setIf('checked', $this->checked);

        return $this;
    }
}
