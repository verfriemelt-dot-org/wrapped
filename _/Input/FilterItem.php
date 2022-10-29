<?php

    declare(strict_types=1);

namespace verfriemelt\wrapped\_\Input;

    use function mb_strlen;

use verfriemelt\wrapped\_\Exception\Input\InputException;
    use verfriemelt\wrapped\_\Http\ParameterBag;

    class FilterItem
    {
        private $input = 'query';

        private $valueName = null;

        private $optional = false;

        private $minLength = false;

        private $maxLength = false;

        private $allowedChars = false;

        private $allowedValues = null;

        private $allowMultipleValuesSent = false;

        protected ParameterBag $parameter;

        public function __construct(ParameterBag $params)
        {
            $this->parameter = $params;
        }

        /**
         * @throws InputException
         */
        public function validate(): bool
        {
            if (!$this->parameter->has($this->valueName) && $this->optional === true) {
                return true;
            }

            if (!$this->parameter->has($this->valueName)) {
                throw new InputException("input not present [{$this->valueName}]");
            }

            $input = $this->parameter->get($this->valueName);

            if (is_array($input)) {
                if (!$this->allowMultipleValuesSent) {
                    throw new InputException("inputtype not allowed [{$this->valueName}]");
                }

                foreach ($input as $inputItem) {
                    $this->checkValues($inputItem);
                }
            } else {
                $this->checkValues($input);
            }

            return true;
        }

        private function checkValues(mixed $input): void
        {
            // filter sent arrays like &msg[]=foobar
            if (!is_string($input) && !is_integer($input)) {
                throw new InputException("input type is wrong [{$this->valueName}]");
            }

            if ($this->minLength && mb_strlen($input, 'UTF-8') < $this->minLength) {
                throw new InputException("input to short [{$this->valueName}]");
            }

            if ($this->maxLength && mb_strlen($input, 'UTF-8') > $this->maxLength) {
                throw new InputException("input to long [{$this->valueName}]");
            }

            // validate content
            if ($this->allowedChars !== false) {
                for ($i = 0; $i < mb_strlen($input, 'UTF-8'); ++$i) {
                    if (strstr($this->allowedChars, $input[$i]) === false) {
                        throw new InputException("not allowed chars within [{$this->valueName}]");
                    }
                }
            }

            if ($this->allowedValues !== null) {
                if (!in_array($input, $this->allowedValues)) {
                    throw new InputException('input not within the specified values');
                }
            }
        }

        /**
         * sets the name of the datafield in the request, eg. $_GET["varname"]
         */
        public function this(string $valueName): static
        {
            $this->valueName = $valueName;
            return $this;
        }

        /**
         * requires a variable name to be in the request
         */
        public function has(string $valueName): static
        {
            return $this->this($valueName);
        }

        public function required(bool $bool = true): static
        {
            return $this->optional(!$bool);
        }

        /**
         * allow values sent as array &foo[]=bar&foo[]=foobar
         */
        public function multiple(bool $bool = true): static
        {
            $this->allowMultipleValuesSent = $bool;
            return $this;
        }

        public function optional(bool $bool = true): static
        {
            $this->optional = $bool;
            return $this;
        }

        public function minLength(int $int = 1): static
        {
            $this->minLength = $int;
            return $this;
        }

        public function maxLength(int $int = 1): static
        {
            $this->maxLength = $int;
            return $this;
        }

        public function allowedChars(string $chars = 'abcdefghijklmnopqrstuvwxyz'): static
        {
            $this->allowedChars = $chars;
            return $this;
        }

        /**
         * sets allowed values like [ "ja","nein"]
         *
         * @return static
         */
        public function allowedValues(array $values)
        {
            $this->allowedValues = $values;
            return $this;
        }

        public function addAllowedValue($value)
        {
            $this->allowedValues[] = $value;
            return $this;
        }
    }
