<?php namespace Wrapped\_\Http\Response;

    class HttpHeader {

        private $name;
        private $value;
        private $replaces = true;

        public function __construct( $name, $value ) {
            $this->name = $name;
            $this->value = $value;
        }

        public function replace( $bool = true ) {
            $this->replaces = $bool;
            return $this;
        }
        
        public function replaces() {
            return $this->replaces;
        }

        public function getName() {
            return $this->name;
        }

        public function getValue() {
            return $this->value;
        }

        public function setName( $name ) {
            $this->name = $name;
            return $this;
        }

        public function setValue( $value ) {
            $this->value = $value;
            return $this;
        }

    }
