<?php

    namespace Wrapped\_;

    class ObjectAnalyser {

        private $object;

        /** @var \ReflectionClass */
        private $reflection;
        private static $_cache = [];

        /**
         * @return static
         * @param type $object
         * @throws \Exception
         */
        public function __construct( $object ) {
            $this->object     = $object;
            $this->reflection = new \ReflectionClass( $this->object );
        }

        public function getObjectShortName() {
            return $this->reflection->getShortName();
        }

        public function getMethods() {
            return $this->reflection->getMethods();
        }

        /**
         * finds a method with the given name
         * @param type $name
         * @return \ReflectionMethod
         */
        public function findMethodByName( $name ) {

            foreach ( $this->getMethods() as $method ) {
                if ( $method->name == $name ) {
                    return $method;
                }
            }

            return false;
        }

        public function getObject() {
            return $this->object;
        }

        public function fetchSetters() {

            $setters = [];
            $methods = $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC );

            $filter = [ "id" ];

            foreach ( $methods as $method ) {

                // if static method, we wont save this to database
                if ( $method->isStatic() ) {
                    continue;
                }

                // only load on getter
                if ( substr( $method->name, 0, 3 ) !== "set" ) {
                    continue;
                }

                $column = lcfirst( substr( $method->name, 3 ) );

                if ( in_array( $column, $filter ) ) {
                    continue;
                }

                $setters[] = [ "setter" => $method->name, "column" => $column ];
            }

            return $setters;
        }

        public function fetchAllColumns() {

            $columns = [];

            foreach ( $this->reflection->getProperties() as $property ) {

                if ( !$property->isStatic() && substr( $property->name, 0, 1 ) !== "_" ) {
                    $columns[] = $property->name;
                }
            }

            return $columns;
        }

        public function fetchColumnsWithGetters() {

            if ( isset( static::$_cache["fetchColumnsWithGetters"][$this->reflection->getName()] ) ) {
                return static::$_cache["fetchColumnsWithGetters"][$this->reflection->getName()];
            }

            $getters = [];
            $methods = $this->reflection->getMethods( \ReflectionMethod::IS_PUBLIC );

            foreach ( $methods as $method ) {

                // if static method, we wont save this to database
                if ( $method->isStatic() || substr( $method->name, 0, 3 ) !== "get" ) {
                    continue;
                }

                $column = lcfirst( substr( $method->name, 3 ) );


                $getters[] = [ "getter" => $method->name, "column" => "$column" ];
            }

            static::$_cache["fetchColumnsWithGetters"][$this->reflection->getName()] = $getters;

            return $getters;
        }

    }
