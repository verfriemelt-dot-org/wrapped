<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Input;

    use \verfriemelt\wrapped\_\Exception\Input\InputException;

    class Filter {

        private $name;

        private $failed = false;

        private $messageStack = [];

        private $filterItems = [];

        public function __construct( $name = null ) {
            $this->name = $name;
        }

        /**
         *
         * @return boolean
         */
        public function validate() {

            foreach ( $this->filterItems as $item ) {

                try {
                    !$item->validate();
                } catch ( InputException $inputException ) {
                    $this->failed         = true;
                    $this->messageStack[] = $inputException->getMessage();
                }
            }

            return !$this->failed;
        }

        public function getMessageStack() {
            return $this->messageStack;
        }

        /**
         *
         * @param FilterItem $item
         * @return Filter
         */
        public function addFilter( FilterItem $item ) {
            $this->filterItems[] = $item;
            return $this;
        }

        /**
         *
         * @param type $what
         * @return FilterItem */
        private function createFilterItem( $what = "query" ) {

            $item = new FilterItem( $what );
            $this->addFilter( $item );

            return $item;
        }

        /**
         * @return FilterItem */
        public function query() {
            return $this->createFilterItem( "query" );
        }

        /**
         * @return FilterItem */
        public function request() {
            return $this->createFilterItem( "request" );
        }

        /**
         * @return FilterItem */
        public function cookies() {
            return $this->createFilterItem( "cookies" );
        }

        /**
         * @return FilterItem */
        public function server() {
            return $this->createFilterItem( "server" );
        }

        /**
         * @return FilterItem */
        public function files() {
            return $this->createFilterItem( "files" );
        }

        /**
         * @return FilterItem */
        public function content() {
            return $this->createFilterItem( "content" );
        }

    }
