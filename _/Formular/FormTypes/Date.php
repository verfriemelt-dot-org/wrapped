<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Formular\FormTypes;

use DateTime;
use Override;

class Date extends FormType
{
    protected string $type = 'date';

    #[Override]
    public function loadTemplate(): static
    {
        $this->tpl->parse(\file_get_contents(\dirname(__DIR__) . '/Template/Date.tpl.php'));
        return $this;
    }

    #[Override]
    public function parseValue($input): ?DateTime
    {
        $parsedTime = DateTime::createFromFormat('Y-m-d', $input);

        if ($parsedTime) {
            $parsedTime->setTime(0, 0, 0, 0);
            return $parsedTime;
        }

        return null;
    }

    #[Override]
    public function fetchHtml(): string
    {
        $this->writeTplValues();
        return $this->tpl->render();
    }
}
