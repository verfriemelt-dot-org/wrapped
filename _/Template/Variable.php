<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Template;

    use \Closure;
    use \verfriemelt\wrapped\_\Output\Viewable;

    class Variable
    implements TemplateItem {

        public $name, $value, $formatCallback;

        private static $formats = [];

        public function __construct( string $name = null, $value = null ) {

            $this->name = $name;

            if ( $value instanceof Viewable ) {
                $this->value = $value->getContents();
            } else {
                $this->value = $value;
            }
        }

        public function readValue() {

            if ( !$this->value instanceof Closure ) {
                return $this->value;
            }

            return call_user_func( $this->value );
        }

        public function readFormattedValue( $formatter ) {

            if ( isset( static::$formats[$formatter] ) ) {

                $formatter = static::$formats[$formatter];
                return $formatter( $this->readValue() );
            }

            return $this->readValue();
        }

        static public function registerFormat( $name, $function ) {
            static::$formats[$name] = $function;
        }

        public function run( &$source ) {

            preg_match_all( '~{{( ?(?<value>' . $this->name . ')(?:\|(?<format>[a-zA-Z0-9]+))? ?)}}~', $source, $hits, PREG_SET_ORDER );

            foreach ( $hits as $row ) {

                if ( isset( $row["format"] ) && isset( self::$formats[$row["format"]] ) ) {

                    $formatter = self::$formats[$row["format"]];
                    $value     = $formatter( $this->readValue() );
                } else {
                    $value = $this->readValue();
                }

                $source = str_replace( $row[0], $value, $source );
            }
        }

        public function getName() {
            return $this->name;
        }

        public function getValue() {
            return $this->value;
        }

        public function setName( $name ) {
            $this->name = $name;
        }

        public function setValue( $value ) {
            $this->value = $value;
        }

    }
