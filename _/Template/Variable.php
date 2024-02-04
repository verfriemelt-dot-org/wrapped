<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template;

use Closure;
use verfriemelt\wrapped\_\Output\Viewable;
use Override;

class Variable implements TemplateItem
{
    public $name;
    public $value;
    public $formatCallback;

    private static array $formats = [];

    public function __construct(?string $name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }

    public function readValue()
    {
        if ($this->value instanceof Closure) {
            return call_user_func($this->value);
        }

        if ($this->value instanceof Viewable) {
            return $this->value->getContents();
        }

        return $this->value;
    }

    public function readFormattedValue($formatter)
    {
        if (isset(static::$formats[$formatter])) {
            $formatter = static::$formats[$formatter];
            return $formatter($this->readValue());
        }

        return $this->readValue();
    }

    public static function registerFormat($name, $function)
    {
        static::$formats[$name] = $function;
    }

    #[Override]
    public function run(&$source)
    {
        preg_match_all(
            '~{{( ?(?<value>' . $this->name . ')(?:\|(?<format>[a-zA-Z0-9]+))? ?)}}~',
            (string) $source,
            $hits,
            PREG_SET_ORDER
        );

        foreach ($hits as $row) {
            if (isset($row['format']) && isset(self::$formats[$row['format']])) {
                $formatter = self::$formats[$row['format']];
                $value = $formatter($this->readValue());
            } else {
                $value = $this->readValue();
            }

            $source = str_replace($row[0], $value, (string) $source);
        }
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }
}
