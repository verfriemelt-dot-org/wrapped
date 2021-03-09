<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Formular\FormTypes;

    use \verfriemelt\wrapped\_\Formular\FormTypes\Text;

    class Textarea
    extends Text {

        public $type = "textarea";

        public $placeholder;

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Textarea.tpl.php" );
            return $this;
        }

        public function placeholder( $placeholder ): Text {
            $this->placeholder = $placeholder;
            return $this;
        }

        public function fetchHtml(): string {

            $this->writeTplValues();

            if ( $this->placeholder ) {
                $this->tpl->setIf( "placeholder" );
                $this->tpl->set( "placeholder", $this->placeholder );
            }

            return $this->tpl->run();
        }

    }
