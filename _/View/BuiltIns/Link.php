<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\View\BuiltIns;

use verfriemelt\wrapped\_\View\View;

class Link extends View
{
    protected string $name;

    protected string $destination;

    public ?string $inlineTemplate = '<a href="{{ destination }}">{{ name }}</a>';

    public function __construct(
        string $name,
        string $destination
    ) {
        parent::__construct();

        $this->name = $name;
        $this->destination = $destination;
    }

    protected function prepare(): void
    {
        $this->tpl->set('name', $this->name);
        $this->tpl->set('destination', $this->destination);
    }

    public function getTemplatePath(): string
    {
        return '';
    }
}
