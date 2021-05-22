<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DI;

    use \Exception;

    class ArgumentMetadata {

        private string $name;

        private ?string $type = null;

        private bool $hasDefaultValue;

        private mixed $defaultValue;

        public function __construct( string $name, ?string $type, bool $hasDefaultValue = false, mixed $defaultValue = null ) {

            $this->name            = $name;
            $this->type            = $type;
            $this->hasDefaultValue = $hasDefaultValue;

            if ( $this->hasDefaultValue ) {
                $this->defaultValue = $defaultValue;
            }
        }

        public function getName(): string {
            return $this->name;
        }

        public function getType(): ?string {
            return $this->type;
        }

        public function hasDefaultValue(): bool {
            return $this->hasDefaultValue;
        }

        public function getDefaultValue(): mixed {

            if ( !$this->hasDefaultValue ) {
                throw new Exception( sprintf( 'Argument »%s« has no default value', $this->name ) );
            }

            return $this->defaultValue;
        }

    }
