<?php

    namespace Wrapped\_;

    use \ReflectionClass;
    use \ReflectionException;
    use \ReflectionMethod;
    use \ReflectionProperty;

    class ObjectAnalyser {

        /** @var ReflectionClass */
        private ReflectionClass $reflection;

        /** @var ReflectionProperty[] */
        private $publicPropertiesExtended = [];
        private $publicProperties         = [];

        public function __construct( $object ) {
            $this->inspect( $object );
        }

        private function inspect( $object ) {

            $this->reflection = new ReflectionClass( $object );

            foreach ( $this->reflection->getProperties() as $prop ) {

                if ( !$prop->isPublic() ) {
                    continue;
                }

                try {

                    // check if getters and setters are present
                    $this->reflection->getMethod( 'get' . ucfirst( $prop->getName() ) );
                    $this->reflection->getMethod( 'set' . ucfirst( $prop->getName() ) );
                } catch ( ReflectionException $e ) {

                    // if not, continue
                    continue;
                }

                $this->publicProperties[] = $prop->getName();

                $this->publicPropertiesExtended[$prop->getName()] = [
                    "setter" => 'set' . ucfirst( $prop->getName() ),
                    "getter" => 'get' . ucfirst( $prop->getName() ),
                    "type"   => $prop->getType() ? $prop->getType()->getName() : null,
                    "column" => $prop->getName()
                ];
            }
        }

        public function getObjectShortName() {
            return $this->reflection->getShortName();
        }

        /**
         * finds a method with the given name
         * @param type $name
         * @return ReflectionMethod
         */
        public function findMethodByName( string $name ) {

            foreach ( $this->reflection->getMethods() as $method ) {

                if ( $method->name == $name ) {
                    return $method;
                }
            }

            return false;
        }

        public function fetchSetters() {
            return $this->publicPropertiesExtended;
        }

        public function fetchAllColumns() {
            return $this->publicProperties;
        }

        public function fetchColumnsWithGetters() {
            return $this->fetchSetters();
        }

        public function fetchAttributes() {
            return $this->publicPropertiesExtended;
        }

        public function fetchAttributeInformation( string $name ) {
            return $this->publicPropertiesExtended[ $name ];
        }

        public function fetchAttributeInformationType( string $name ) {
            return $this->publicPropertiesExtended[ $name ]['type'];
        }
    }
