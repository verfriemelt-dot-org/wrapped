<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Input;

use verfriemelt\wrapped\_\Exception\Input\InputException;
use verfriemelt\wrapped\_\Http\Request\RequestStack;
use verfriemelt\wrapped\_\ParameterBag;

class Filter
{
    private bool $failed = false;

    private array $messageStack = [];

    private array $filterItems = [];

    public function __construct(
        private readonly RequestStack $requestStack
    ) {}

    /**
     * @return bool
     */
    public function validate()
    {
        foreach ($this->filterItems as $item) {
            try {
                !$item->validate();
            } catch (InputException $inputException) {
                $this->failed = true;
                $this->messageStack[] = $inputException->getMessage();
            }
        }

        return !$this->failed;
    }

    public function getMessageStack()
    {
        return $this->messageStack;
    }

    /**
     * @return Filter
     */
    public function addFilter(FilterItem $item)
    {
        $this->filterItems[] = $item;
        return $this;
    }

    private function createFilterItem(ParameterBag $bag): FilterItem
    {
        $item = new FilterItem($bag);
        $this->addFilter($item);

        return $item;
    }

    public function query(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->query());
    }

    public function request(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->request());
    }

    public function cookies(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->cookies());
    }

    public function server(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->server());
    }

    public function files(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->files());
    }

    public function content(): FilterItem
    {
        return $this->createFilterItem($this->requestStack->getCurrentRequest()->content());
    }
}
