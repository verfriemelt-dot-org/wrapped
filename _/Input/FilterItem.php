<?php

    namespace Wrapped\_\Input;

use \Wrapped\_\Exception\Input\InputException;
use \Wrapped\_\Http\Request\Request;

    class FilterItem {

        private $input = "query";
        private $valueName = null;
        private $optional = false;
        private $minLength = false;
        private $maxLength = false;
        private $allowedChars = false;
        private $allowedValues = null;
        private $allowMultipleValuesSent = false;

        public function __construct( $input ) {
            $this->input = $input;
        }

        /**
         *
         * @return boolean
         * @throws InputException
         */
        public function validate() {

            switch ( $this->input ) {
                case "query" : $dataSource = Request::getInstance()->query();
                    break;
                case "request" : $dataSource = Request::getInstance()->request();
                    break;
                case "files" : $dataSource = Request::getInstance()->files();
                    break;
                case "server" : $dataSource = Request::getInstance()->server();
                    break;
                case "content" : $dataSource = Request::getInstance()->content();
                    break;
                case "attributes" : $dataSource = Request::getInstance()->attributes();
                    break;
                case "cookie" : $dataSource = Request::getInstance()->cookie();
                    break;
                default: return false;
            }

            if ( !$dataSource->has( $this->valueName ) && $this->optional === true ) {
                return true;
            }

            if ( !$dataSource->has( $this->valueName ) ) {
                throw new InputException( "input not present [{$this->valueName}]" );
            }

            $input = $dataSource->get( $this->valueName );

            if ( is_array( $input ) ) {

                if ( !$this->allowMultipleValuesSent ) {
                    throw new InputException( "inputtype not allowed [{$this->valueName}]" );
                }

                foreach( $input as $inputItem ) {
                    $this->checkValues( $inputItem );
                }
            } else {
                $this->checkValues( $input );
            }

            return true;
        }

        private function checkValues( $input ) {
            // filter sent arrays like &msg[]=foobar
            if ( !is_string( $input ) && !is_integer( $input ) ) {
                throw new InputException( "input type is wrong [{$this->valueName}]" );
            }

            if ( $this->minLength && mb_strlen( $input, 'UTF-8' ) < $this->minLength ) {
                throw new InputException( "input to short [{$this->valueName}]" );
            }

            if ( $this->maxLength && mb_strlen( $input, 'UTF-8' ) > $this->maxLength ) {
                throw new InputException( "input to long [{$this->valueName}]" );
            }


            //validate content
            if ( $this->allowedChars !== false ) {
                for ( $i = 0; $i < mb_strlen( $input, 'UTF-8' ); $i++ ) {
                    if ( strstr( $this->allowedChars, $input[$i] ) === false )
                        throw new InputException( "not allowed chars within [{$this->valueName}]" );
                }
            }

            if ( $this->allowedValues !== null ) {


                if ( !in_array( $input, $this->allowedValues ) ) {
                    throw new InputException( "input not within the specified values" );
                }
            }
        }

        /**
         * sets the name of the datafield in the request, eg. $_GET["varname"]
         * @param type $valueName
         * @return FilterItem
         */
        public function this( $valueName ) {
            $this->valueName = $valueName;
            return $this;
        }

        /**
         * requires a variable name to be in the request
         * @param type $valueName
         * @return FilterItem
         */
        public function has( $valueName ) {
            return $this->this( $valueName );
        }

        /**
         *
         * @param type $bool
         * @return static
         */
        public function required( $bool = true ) {
            return $this->optional( !$bool );
        }

        /**
         * allow values sent as array &foo[]=bar&foo[]=foobar
         * @param type $bool
         * @return static
         */
        public function multiple( $bool = true ) {
            $this->allowMultipleValuesSent = $bool;
            return $this;
        }

        /**
         *
         * @param type $bool
         * @return FilterItem
         */
        public function optional( $bool = true ) {
            $this->optional = $bool;
            return $this;
        }

        /**
         *
         * @param type $int
         * @return FilterItem
         */
        public function minLength( $int = 1 ) {
            $this->minLength = $int;
            return $this;
        }

        /**
         *
         * @param type $int
         * @return FilterItem
         */
        public function maxLength( $int = 1 ) {
            $this->maxLength = $int;
            return $this;
        }

        /**
         *
         * @param type $chars
         * @return FilterItem
         */
        public function allowedChars( $chars = "abcdefghijklmnopqrstuvwxyz" ) {
            $this->allowedChars = $chars;
            return $this;
        }

        /**
         * sets allowed values like [ "ja","nein"]
         * @param array $values
         * @return static
         */
        public function allowedValues( array $values ) {
            $this->allowedValues = $values;
            return $this;
        }

        public function addAllowedValue( $value ) {
            $this->allowedValues[] = $value;
            return $this;
        }

    }
