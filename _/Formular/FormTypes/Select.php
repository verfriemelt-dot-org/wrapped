<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Formular\FormTypes;

    use verfriemelt\wrapped\_\Template\Repeater;

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

        private function writeOption( Repeater $repeater, SelectItem $option ) {
            $repeater->set( "name", $option->name );
            $repeater->set( "value", $option->value );
            $repeater->setIf( "selected", $this->getValue() === $option->value );
            $repeater->setIf( "option" );

            $repeater->save();
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




