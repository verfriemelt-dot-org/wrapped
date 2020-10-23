<?php

    namespace Wrapped\_\DataModel;

    use \Wrapped\_\NamingConvention\CamelCase;
    use \Wrapped\_\NamingConvention\Convention;
    use \Wrapped\_\NamingConvention\LowerCase;

    class DataModelAttribute {

        private string $name;

        private string $setter;

        private string $getter;

        private Convention $case;

        private ?string $type = null;

        public function __construct( string $name, ?Convention $case = null ) {
            $this->name = $name;
            $this->case = (new CamelCase( $name ) )->convertTo( $case ?? LowerCase::class );
        }

        public function getName(): string {
            return $this->name;
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

    }
