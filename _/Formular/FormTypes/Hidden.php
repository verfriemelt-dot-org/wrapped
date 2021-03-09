<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Formular\FormTypes;

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

            $this->tpl->set( "cssClasses", implode( " ", $this->cssClasses ) );

            return $this->tpl->run();
        }

    }
