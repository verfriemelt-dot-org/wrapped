<?php

    declare(strict_types = 1);

    namespace Wrapped\_\DataModel;

    use \Wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \Wrapped\_\DataModel\Attribute\Naming\Convention;
    use \Wrapped\_\DataModel\Attribute\Naming\Rename;
    use \Wrapped\_\DataModel\Attribute\Naming\SnakeCase;

    class DataModelAttribute {

        private string $name;

        private string $setter;

        private string $getter;

        private Convention $case;

        private ?string $type = null;

        private Rename $renamed;

        public function __construct( string $name, ?Convention $case = null ) {
            $this->name = $name;
            $this->case = (new CamelCase( $name ) )->convertTo( $case ?? SnakeCase::class );
        }

        public function isRenamed(): bool {
            return isset( $this->renamed );
        }

        public function setRenamed( Rename $renamed ) {
            $this->renamed = $renamed;
            return $this;
        }

        public function fetchDatabaseName(): string {

            if ( $this->isRenamed() ) {
                return $this->renamed->name;
            }

            return $this->getNamingConvention()->getString();
        }

        function getSetter(): string {
            return $this->setter;
        }

        function getGetter(): string {
            return $this->getter;
        }

        function setSetter( string $setter ): void {
            $this->setter = $setter;
        }

        function setGetter( string $getter ): void {
            $this->getter = $getter;
        }

        function getType(): ?string {
            return $this->type;
        }

        function setType( ?string $type ): void {
            $this->type = $type;
        }

        public function getNamingConvention(): Convention {
            return $this->case;
        }

        public function getName(): string {
            return $this->name;
        }

    }
