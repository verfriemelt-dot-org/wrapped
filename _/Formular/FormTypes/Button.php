<?php

    namespace Wrapped\_\Formular\FormTypes;

    class Button
    extends FormType {

        public $type = "button";

        public function __construct( string $name, string $value = null, \Wrapped\_\Template\Template $template = null ) {
            parent::__construct( $name, $value, $template );

            $this->addCssClass("btn btn-default");
        }

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Button.tpl.php" );
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

    }
