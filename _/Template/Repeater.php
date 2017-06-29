<?php

    namespace Wrapped\_\Template;

    class Repeater
    implements TemplateItem {

        public $name = '';
        public $data = [ ];
        private $currentDataLine = null;
        private $foundRepeater = [ ];

        public function __construct( $name ) {
            $this->name = $name;
        }

        /**
         * set an assoc array as new row
         * @param array $data
         */
        public function setArray( array $data ) {
            foreach ( $data as $key => $value ) {
                $this->set( $key, $value );
            }

            return $this;
        }

        /**
         *
         * @param type $name
         * @param type $value
         * @return \Wrapped\_\Template\Repeater
         */
        public function set( $name, $value ) {
            $this->currentDataLine["vars"][$name] = new Variable( $name, $value );
            return $this;
        }

        /**
         * save the current line of data
         * @deprecated since version number
         * @return integer current index number of entry
         */
        public function saveDataRow() {
            return $this->save();
        }

        /**
         * save the current line of data
         * @return integer current index number of entry
         */
        public function save() {
            $this->data[] = $this->currentDataLine;
            $this->currentDataLine = [ ];
            return $this;
        }

        /**
         *
         * @param string $name
         * @param bool $bool
         * @return \Wrapped\_\Template\Repeater
         */
        public function setIf( $name, $bool = true ) {
            $this->currentDataLine["if"][$name] = new Ifelse( $name, $bool );
            return $this;
        }

        /**
         * creates new children or returns the current one
         * @param string $name
         * @param bool $bool
         * @return \Wrapped\_\Template\Repeater
         */
        public function createChildRepeater( $name ) {
            if ( !isset( $this->currentDataLine["repeater"][$name] ) ) {
                $this->currentDataLine["repeater"][$name] = new Repeater( $name );
            }

            return $this->currentDataLine["repeater"][$name];
        }

        public function run( &$html ) {

            $this->findRepeater( $html );

            foreach ( $this->foundRepeater as $repeater ) {

                //replace all the data within
                $replacedDataWithinRepeaterString = $this->replaceDataWithinRepeater( $repeater );

                //save changes
                $html = str_replace( $repeater[0], $replacedDataWithinRepeaterString, $html );
            }
        }

        public function findRepeater( &$html ) {
            if ( !empty( $this->foundRepeater ) )
                return;

            //grep repeater
            preg_match_all(
                "~{{ ?repeater=['\"]$this->name['\"] ?}}(.*){{ ?/repeater=['\"]$this->name['\"] ?}}~sU", $html, $this->foundRepeater, PREG_SET_ORDER
            );
        }

        public function replaceDataWithinRepeater( $repeater ) {

            $buffer = '';

            foreach ( $this->data as $row ) {

                //matched data
                $mangledData = $repeater[1];

                // normal variables
                if ( isset( $row["vars"] ) ) {
                    foreach ( $row["vars"] as $item ) {
                        //replace simple variables
                        $item->run( $mangledData );
                    }
                }

                // run objects (subrepeater, subifs)
                if ( isset( $row["repeater"] ) ) {
                    foreach ( $row["repeater"] as $obj ) {
                        $obj->run( $mangledData );
                    }
                }

                // run objects (subrepeater, subifs)
                if ( isset( $row["if"] ) ) {
                    foreach ( $row["if"] as $obj ) {
                        $obj->run( $mangledData );
                    }
                }

                $buffer .= $mangledData;
            }

            return $buffer;
        }

    }
