<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\DataModel;

    use \ReflectionClass;
    use \ReflectionException;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\Convention;
    use \verfriemelt\wrapped\_\DataModel\Attribute\Naming\PascalCase;

    class DataModelAnalyser {

        private ReflectionClass $reflection;

        private ?array $properties = null;

        public function __construct( DataModel|string $model ) {
            $this->reflection = new ReflectionClass( $model );
        }

        /**
         *
         * @return DataModelProperty[]
         */
        public function fetchProperties() {

            if ( $this->properties === null ) {
                $this->prepareProperties();
            }

            return $this->properties ?? [];
        }

        public function fetchPropertyByName( string $name ): DataModelProperty {

            if ( $this->properties === null ) {
                $this->prepareProperties();
            }

            foreach ( $this->properties as $prop ) {
                if ( $prop->getName() == $name ) {
                    return $prop;
                }
            }

            throw new \Exception( "prop »{$name}« not found " );
        }

        public function getBaseName(): string {
            return basename( str_replace( '\\', '/', $this->reflection->name ) );
        }

        public function getStaticName(): string {
            return $this->reflection->name;
        }

        public function fetchTableNamingConvention(): Convention {
            return $this->fetchNamingConventionAttributes( $this->reflection ) ? $this->fetchNamingConventionAttributes( $this->reflection )->newInstance() : new PascalCase();
        }

        public function fetchNamingConventionAttributes( $element ): ?\ReflectionAttribute {
            return $attributes = $element->getAttributes( Convention::class, \ReflectionAttribute::IS_INSTANCEOF )[0] ?? null;
        }

        public function fetchNameOverride( $element ): ?\ReflectionAttribute {
            return $attributes = $element->getAttributes( Attribute\Naming\Rename::class, \ReflectionAttribute::IS_INSTANCEOF )[0] ?? null;
        }

        protected function prepareProperties() {

            $hasDataModelAttribute = false;

            foreach ( $this->reflection->getProperties() as $property ) {

                $name = $property->getName();

                // ignore underscore attributes
                if ( $name[0] == "_" ) {
                    continue;
                }

                $case       = new CamelCase( $name );
                $getterName = CamelCase::fromStringParts( ... [
                        'get',
                        ... $case->fetchStringParts()
                    ] )->getString();

                $setterName = CamelCase::fromStringParts( ... [
                        'set',
                        ... $case->fetchStringParts()
                    ] )->getString();

                try {
                    $getter = $this->reflection->getMethod( $getterName )->getName();
                    $setter = $this->reflection->getMethod( $setterName )->getName();
                } catch ( ReflectionException $e ) {

                    // both setters and getters must be present to be a valid property
                    continue;
                }

                $convetion = $this->fetchNamingConventionAttributes( $property );

                $dma = new DataModelProperty( $name, $convetion ? $convetion->newInstance() : null );
                $dma->setGetter( $getter );
                $dma->setSetter( $setter );

                $renamedAttribute = $this->fetchNameOverride( $property );

                if ( $renamedAttribute ) {
                    $dma->setRenamed( $renamedAttribute->newInstance() );
                }

                if ( $property->getType() ) {
                    $dma->setType( $property->getType()->getName() );
                }

                $this->properties[] = $dma;
            }
        }

    }
