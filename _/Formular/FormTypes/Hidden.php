<?php

    namespace Wrapped\_\Formular\FormTypes;

    class Hidden
    extends FormType {

        public $type = "hidden";

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Hidden.tpl.php" );
            return $this;
        }

        public function fetchHtml(): string {

            $this->tpl->set( "value", $this->value );
            $this->tpl->set( "name", $this->name );
            $this->tpl->set( "id", $this->name );

            return $this->tpl->run();
        }

    }
