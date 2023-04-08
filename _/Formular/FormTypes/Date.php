<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use DateTime;

class Date extends FormType
{
    public $type = 'date';

    public function loadTemplate(): FormType
    {
        $this->tpl->parseFile(dirname(__DIR__) . '/Template/Date.tpl.php');
        return $this;
    }

    public function setValue($value): FormType
    {
        if ($value instanceof DateTime) {
            $this->value = $value->format('Y-m-d');
        } else {
            $this->value = $value;
        }

        return $this;
    }

    public function parseValue($input)
    {
        $parsedTime = DateTime::createFromFormat('Y-m-d', $input);

        if ($parsedTime) {
            $parsedTime->setTime(0, 0, 0, 0);
            return $parsedTime;
        }

        return null;
    }

    public function fetchHtml(): string
    {
        $this->writeTplValues();
        return $this->tpl->run();
    }
}
