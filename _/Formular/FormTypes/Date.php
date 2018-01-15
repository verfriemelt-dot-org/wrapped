<?php

    namespace Wrapped\_\Formular\FormTypes;

    use \DateTime;
    use \Wrapped\_\Formular\FormTypes\FormType;

    class Date
    extends FormType {

        public $type = "date";

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Date.tpl.php" );
            return $this;
        }

        public function setValue( $value ): FormType {

            if ( $value instanceof DateTime ) {
                $this->value = $value->format( "Y-m-d" );
            } else {
                $this->value = $value;
            }

            return $this;
        }

        public function parseValue( $input ) {

            $parsedTime = DateTime::createFromFormat( "Y-m-d", $input );

            if ( $parsedTime ) {
                $parsedTime->setTime(0,0,0,0);
                return $parsedTime;
            }

            return null;
        }

        public function fetchHtml(): string {

            $this->writeTplValues();
            return $this->tpl->run();
        }

    }
