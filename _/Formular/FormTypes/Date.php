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
            $this->value = $value->format( "Y-m-d" );
            return $this;
        }

        public function parseValue( $input ) {
            return DateTime::createFromFormat( "Y-m-d", $input );
        }

        public function fetchHtml(): string {

            $this->writeTplValues();
            return $this->tpl->run();
        }

    }
