<?php

    namespace Wrapped\_\Formular\FormTypes;

    use \Wrapped\_\Input\FilterItem;
    use \Wrapped\_\Template\Template;

    abstract class FormType {

        public $name;
        public $value;
        public $label;
        public $tpl;
        public $type;
        public $pattern;
        public $title;
        public $disabled = false;
        public $readonly = false;
        public $required = false;

        /** @var FilterItem */
        public $filterItem;
        public $cssClasses = [];

        abstract public function loadTemplate(): \Wrapped\_\Formular\FormTypes\FormType;

        abstract public function fetchHtml(): string;

        public function __construct( string $name, string $value = null, Template $template = null ) {

            $this->name  = $name;
            $this->value = $value;

            if ( !$template ) {
                $this->tpl = new Template();
                $this->loadTemplate();
            } else {
                $this->tpl = $template;
            }
        }

        public function setFilterItem( FilterItem $filterItem ) {
            $this->filterItem = $filterItem;
            return $this;
        }

        public function getFilterItem(): FilterItem {
            return $this->filterItem;
        }

        public function setValue( $value ): \Wrapped\_\Formular\FormTypes\FormType {
            $this->value = $value;
            return $this;
        }

        public function getValue() {
            return $this->value;
        }

        public function setOptional() {
            $this->filterItem->optional( true );
            return $this;
        }

        public function label( $label ): FormType {

            $this->label = $label;
            return $this;
        }

        /**
         * sets the element to disabled state;
         * disabled fields will not be sent along with the request
         * @param type $bool
         * @return \Wrapped\_\Formular\FormTypes\FormType
         */
        public function disabled( $bool = true ): FormType {
            $this->disabled = $bool;
            return $this;
        }

        /**
         * sets the element to readonly state;
         * this is just for frontend visuals
         * the field is sent along with the request
         * @param type $bool
         * @return \Wrapped\_\Formular\FormTypes\FormType
         */
        public function readonly( $bool = true ): FormType {
            $this->readonly = $bool;
            return $this;
        }

        protected function writeTplValues(): FormType {

            $this->tpl->set( "value", $this->value );
            $this->tpl->set( "name", $this->name );
            $this->tpl->set( "id", $this->name );
            $this->tpl->set( "type", $this->type );

            $this->tpl->setIf( "disabled", $this->disabled );
            $this->tpl->setIf( "readonly", $this->readonly );
            $this->tpl->setIf( "required", $this->required );

            $this->tpl->set( "label", $this->label );
            $this->tpl->setIf( "displayLabel", !empty( $this->label ) );

            $this->tpl->set( "cssClasses", implode( " ", $this->cssClasses ) );

            $this->tpl->setIf( "pattern", !empty( $this->pattern ) );
            $this->tpl->set( "title", $this->title );
            $this->tpl->set( "pattern", $this->pattern );


            return $this;
        }

        public function addCssClass( $classname ): FormType {
            $this->cssClasses[] = $classname;
            return $this;
        }

        /**
         * sets title used conjunction with pattern
         * @param type $title
         * @return $this
         */
        public function setTitle( $title ) {
            $this->title = $title;
            return $this;
        }

        /**
         * pattern for html5 validation
         * eg ".{5,}" for minimum 5 characters of input
         * @param type $pattern
         * @return $this
         */
        public function setPattern( $pattern ) {
            $this->pattern = $pattern;
            return $this;
        }

        public function required( $bool = true ) {
            $this->required = $bool;
            return $this;
        }

    }
