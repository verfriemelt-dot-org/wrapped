<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use verfriemelt\wrapped\_\DI\Container;
use verfriemelt\wrapped\_\Input\FilterItem;
use verfriemelt\wrapped\_\Template\Template;
use verfriemelt\wrapped\_\Template\TemplateRenderer;

abstract class FormType
{
    public $label;

    public $type;

    public $pattern;

    public $title;

    public $disabled = false;

    public $readonly = false;

    public $required = false;

    public $postAsArray = false;

    /** @var FilterItem */
    public $filterItem;

    public $cssClasses = [];

    abstract public function loadTemplate(): FormType;

    abstract public function fetchHtml(): string;

    public function __construct(
        protected string $name,
        protected ?string $value = null,
        protected Template $tpl = new Template(new TemplateRenderer(new Container()))
    ) {
        $this->loadTemplate();
    }

    public function setFilterItem(FilterItem $filterItem): FormType
    {
        $this->filterItem = $filterItem;
        return $this;
    }

    public function getFilterItem(): FilterItem
    {
        return $this->filterItem;
    }

    public function setValue($value): FormType
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setOptional(): FormType
    {
        $this->filterItem->optional(true);
        return $this;
    }

    public function label($label): FormType
    {
        $this->label = $label;
        return $this;
    }

    /**
     * sets the element to disabled state;
     * disabled fields will not be sent along with the request
     *
     * @param type $bool
     */
    public function disabled($bool = true): FormType
    {
        $this->disabled = $bool;
        return $this;
    }

    /**
     * sets the element to readonly state;
     * this is just for frontend visuals
     * the field is sent along with the request
     *
     * @param type $bool
     */
    public function readonly($bool = true): FormType
    {
        $this->readonly = $bool;
        return $this;
    }

    protected function writeTplValues(): FormType
    {
        $this->tpl->set('value', $this->value);
        $this->tpl->set('name', $this->name);
        $this->tpl->set('postname', $this->name . ($this->postAsArray ? '[]' : ''));
        $this->tpl->set('id', $this->name);
        $this->tpl->set('type', $this->type);

        $this->tpl->setIf('disabled', $this->disabled);
        $this->tpl->setIf('readonly', $this->readonly);
        $this->tpl->setIf('required', $this->required);

        $this->tpl->set('label', $this->label);
        $this->tpl->setIf('displayLabel', $this->label !== null);

        $this->tpl->set('cssClasses', implode(' ', $this->cssClasses));

        $this->tpl->setIf('pattern', !empty($this->pattern));
        $this->tpl->set('title', $this->title);
        $this->tpl->set('pattern', $this->pattern);

        return $this;
    }

    public function addCssClass($classname): FormType
    {
        $this->cssClasses[] = $classname;
        return $this;
    }

    /**
     * sets title used conjunction with pattern
     *
     * @param type $title
     *
     * @return $this
     */
    public function setTitle($title): FormType
    {
        $this->title = $title;
        return $this;
    }

    /**
     * pattern for html5 validation
     * eg ".{5,}" for minimum 5 characters of input
     *
     * @param type $pattern
     *
     * @return $this
     */
    public function setPattern($pattern): FormType
    {
        $this->pattern = $pattern;
        return $this;
    }

    public function required($bool = true): FormType
    {
        $this->required = $bool;
        return $this;
    }

    public function parseValue($input)
    {
        return $input;
    }

    public function postAsArray(bool $bool = true): FormType
    {
        $this->postAsArray = $bool;
        return $this;
    }
}
