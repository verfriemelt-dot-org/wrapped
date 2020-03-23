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
        private $properties       = [];
        private $publicProperties = [];

        public function __construct( $object ) {
            $this->inspect( $object );
        }

        private function inspect( $object ) {

            $this->reflection = new ReflectionClass( $object );
            $this->properties = $this->reflection->getProperties();

            foreach ( $this->properties as $prop ) {

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

            $setters = [];

            foreach ( $this->publicProperties as $prop ) {
                $setters[] = [
                    "setter" => 'set' . ucfirst( $prop ),
                    "getter" => 'get' . ucfirst( $prop ),
                    "column" => $prop
                ];
            }

            return $setters;
        }

        public function fetchAllColumns() {
            return $this->publicProperties;
        }

        public function fetchColumnsWithGetters() {
            return $this->fetchSetters();
        }

    }
