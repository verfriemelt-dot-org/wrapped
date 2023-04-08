<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Input;

use verfriemelt\wrapped\_\Exception\Input\InputException;
use verfriemelt\wrapped\_\Http\ParameterBag;
use verfriemelt\wrapped\_\Http\Request\Request;

class Filter
{
    protected Request $request;

    private $failed = false;

    private $messageStack = [];

    private $filterItems = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

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
        return $this->createFilterItem($this->request->query());
    }

    public function request(): FilterItem
    {
        return $this->createFilterItem($this->request->request());
    }

    public function cookies(): FilterItem
    {
        return $this->createFilterItem($this->request->cookies());
    }

    public function server(): FilterItem
    {
        return $this->createFilterItem($this->request->server());
    }

    public function files(): FilterItem
    {
        return $this->createFilterItem($this->request->files());
    }

    public function content(): FilterItem
    {
        return $this->createFilterItem($this->request->content());
    }
}
