<?php

    namespace Wrapped\_\DataModel;

    use \ReflectionClass;
    use \ReflectionException;
    use \Wrapped\_\DataModel\Attribute\Naming\CamelCase;
    use \Wrapped\_\DataModel\Attribute\Naming\Convention;

    class DataModelAnalyser {

        private DataModel $model;

        private ReflectionClass $reflection;

        private ?array $attributes = null;

        public function __construct( DataModel $model ) {
            $this->model      = $model;
            $this->reflection = new ReflectionClass( $model );
        }

        /**
         *
         * @return DataModelAttribute[]
         */
        public function fetchPropertyAttributes(): array {

            if ( $this->attributes === null ) {
                $this->prepareAttributes();
            }

            return $this->attributes ?? [];
        }

        public function getBaseName(): string {
            return basename( str_replace( '\\', '/', $this->reflection->name ) );
        }

        public function getStaticName(): string {
            return $this->reflection->name;
        }

        protected function prepareAttributes() {

            $hasDataModelAttribute = false;

            foreach ( $this->reflection->getProperties() as $attrib ) {

                $name = $attrib->getName();

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
                    continue;
                }

                $attachtedConventionAttributes = $attrib->getAttributes( Convention::class, \ReflectionAttribute::IS_INSTANCEOF );
                $attachtedConventionAttribute  = $attachtedConventionAttributes[0] ?? null;

                $dma = new DataModelAttribute( $name, $attachtedConventionAttribute ? $attachtedConventionAttribute->newInstance() : null );
                $dma->setGetter( $getter );
                $dma->setSetter( $setter );
                $dma->setType( $attrib->getType() );


                $this->attributes[] = $dma;
            }
        }

    }
