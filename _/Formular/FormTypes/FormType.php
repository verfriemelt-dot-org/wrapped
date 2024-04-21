<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Input\FilterItem;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;

abstract class FormType
{
    protected string $label;
    protected string $type;
    protected string $pattern;
    protected string $title;

    protected bool $disabled = false;
    protected bool $readonly = false;
    protected bool $required = false;
    protected bool $postAsArray = false;

    public FilterItem $filterItem;

    /** @var string[] */
    public array $cssClasses = [];

    abstract public function loadTemplate(): static;

    abstract public function fetchHtml(): string;

    public function __construct(
        protected string $name,
        protected ?string $value = null,
        protected Template $tpl = new Template(new TemplateRenderer(new Container())),
    ) {
        $this->loadTemplate();
    }

    public function setFilterItem(FilterItem $filterItem): static
    {
        $this->filterItem = $filterItem;
        return $this;
    }

    public function getFilterItem(): FilterItem
    {
        return $this->filterItem;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setOptional(): static
    {
        $this->filterItem->optional(true);
        return $this;
    }

    public function label($label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * sets the element to disabled state;
     * disabled fields will not be sent along with the request
     */
    public function disabled(bool $bool = true): static
    {
        $this->disabled = $bool;
        return $this;
    }

    /**
     * sets the element to readonly state;
     * this is just for frontend visuals
     * the field is sent along with the request
     */
    public function readonly(bool $bool = true): static
    {
        $this->readonly = $bool;
        return $this;
    }

    protected function writeTplValues(): static
    {
        $this->tpl->set('value', $this->value);
        $this->tpl->set('name', $this->name);
        $this->tpl->set('postname', $this->name . ($this->postAsArray ? '[]' : ''));
        $this->tpl->set('id', $this->name);
        $this->tpl->set('type', $this->type);

        $this->tpl->setIf('disabled', $this->disabled);
        $this->tpl->setIf('readonly', $this->readonly);
        $this->tpl->setIf('required', $this->required);

        if (isset($this->label)) {
            $this->tpl->set('label', $this->label);
            $this->tpl->setIf('displayLabel');
        }

        $this->tpl->set('cssClasses', implode(' ', $this->cssClasses));

        $this->tpl->setIf('pattern', !empty($this->pattern));
        $this->tpl->set('title', $this->title ?? '');
        $this->tpl->set('pattern', $this->pattern ?? '');

        return $this;
    }

    public function addCssClass(string $classname): static
    {
        $this->cssClasses[] = $classname;
        return $this;
    }

    /**
     * sets title used conjunction with pattern
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * pattern for html5 validation
     * eg ".{5,}" for minimum 5 characters of input
     */
    public function setPattern(string $pattern): static
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function required($bool = true): static
    {
        $this->required = $bool;
        return $this;
    }

    public function parseValue(string $input): mixed
    {
        return $input;
    }

    public function postAsArray(bool $bool = true): static
    {
        $this->postAsArray = $bool;
        return $this;
    }
}
