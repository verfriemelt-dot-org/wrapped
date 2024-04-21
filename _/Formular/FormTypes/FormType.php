<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use verfriemelt\wrapped\_\Input\FilterItem;
use verfriemelt\wrapped\_\Template\Template;

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

    protected string $name;
    protected ?string $value = null;

    public FilterItem $filterItem;

    /** @var string[] */
    public array $cssClasses = [];

    public function __construct(
        protected readonly Template $template,
    ) {}

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract protected function loadTemplate(): string;

    abstract public function render(): string;

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
        $this->template->parse($this->loadTemplate());

        $this->template->set('value', $this->value);
        $this->template->set('name', $this->name);
        $this->template->set('postname', $this->name . ($this->postAsArray ? '[]' : ''));
        $this->template->set('id', $this->name);
        $this->template->set('type', $this->type);

        $this->template->setIf('disabled', $this->disabled);
        $this->template->setIf('readonly', $this->readonly);
        $this->template->setIf('required', $this->required);

        if (isset($this->label)) {
            $this->template->set('label', $this->label);
            $this->template->setIf('displayLabel');
        }

        $this->template->set('cssClasses', implode(' ', $this->cssClasses));

        $this->template->setIf('pattern', !empty($this->pattern));
        $this->template->set('title', $this->title ?? '');
        $this->template->set('pattern', $this->pattern ?? '');

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
