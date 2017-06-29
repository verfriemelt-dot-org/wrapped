<?php

    namespace Wrapped\_\Formular\FormTypes;

    class Checkbox
    extends FormType {

        public $type = "checkbox";

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Checkbox.tpl.php" );
            return $this;
        }

        public function type( $type ) {
            $this->type = $type;
            return $this;
        }

        public function fetchHtml(): string {

            $this->writeTplValues();

            return $this->tpl->run();
        }

        protected function writeTplValues(): FormType {

            $this->tpl->setIf( "checked" , $this->value );
            return parent::writeTplValues();
        }

    }
