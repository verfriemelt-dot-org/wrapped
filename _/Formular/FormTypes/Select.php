<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Formular\FormTypes;

    class Select
    extends FormType {

        private $options = [];

        private $optGroups = [];

        public function addOption( $name, $value, $optGroupName = null ) {

            if ( $optGroupName !== null ) {

                if ( !isset( $this->optGroups[$optGroupName] ) ) {
                    $this->optGroups[$optGroupName] = new SelectGroup( $optGroupName );
                    $this->options[]                = $this->optGroups[$optGroupName];
                }

                $currentOptgroup = $this->optGroups[$optGroupName];
                $currentOptgroup->addChild( $this->buildOption( $name, $value ) );

                return $this;
            }

            $this->options[] = $this->buildOption( $name, $value );

            return $this;
        }

        private function buildOption( $name, $value ) {

            if ( $this->filterItem ) {
                $this->filterItem->addAllowedValue( $value );
            }

            return new SelectItem( $name, $value );
        }

        private function writeOption( $r, $o ) {
            $r->set( "name", $o->name );
            $r->set( "value", $o->value );
            $r->setIf( "selected", $this->getValue() == $o->value );
            $r->setIf( "option" );

            $r->save();
        }

        public function fetchHtml(): string {

            $optionsRepeater = $this->tpl->createRepeater( "options" );

            foreach ( $this->options as $entry ) {

                if ( $entry instanceof SelectGroup ) {

                    $optionsRepeater->setIf( "openOptGroup" );
                    $optionsRepeater->set( "optGroupName", $entry->name );
                    $optionsRepeater->save();

                    foreach ( $entry->fetchChildren() as $children ) {
                        $this->writeOption( $optionsRepeater, $children );
                    }

                    $optionsRepeater->setIf( "closeOptGroup" );
                    $optionsRepeater->save();
                } else {
                    $this->writeOption( $optionsRepeater, $entry );
                }
            }

            $this->writeTplValues();

            return $this->tpl->run();
        }

        public function loadTemplate(): FormType {
            $this->tpl->parseFile( dirname( __DIR__ ) . "/Template/Select.tpl.php" );
            return $this;
        }

    }

    class SelectItem {

        public $name, $value;

        public function __construct( $name, $value ) {
            $this->name  = $name;
            $this->value = $value;
        }

    }

    class SelectGroup {

        public $name;

        private $children = [];

        public function __construct( $name ) {
            $this->name = $name;
        }

        public function addChild( SelectItem $item ) {
            $this->children[] = $item;
            return $this;
        }

        public function fetchChildren() {
            return $this->children;
        }

    }
