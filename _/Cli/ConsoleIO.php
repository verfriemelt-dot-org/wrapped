<?php

    namespace Wrapped\_\Cli;

    class ConsoleIO {

        private $prefixFunc;
        private $selectedStream;
        private $stdout = STDOUT;
        private $stderr = STDERR;
        private $hadLineOutput = false;

        public function __construct() {
            $this->selectedStream = &$this->stdout;
        }

        public function setPrefixCallback( callable $func ) {
            $this->prefixFunc = $func;
            return $this;
        }

        public function toSTDOUT() {
            $this->selectedStream = &$this->stdout;
        }

        public function toSTDERR() {
            $this->selectedStream = &$this->stderr;
        }

        public function write( $text ) {

            if ( $this->hadLineOutput !== true ) {
                fwrite( $this->selectedStream, ($this->prefixFunc)() );
                $this->hadLineOutput = true;
            }

            fwrite( $this->selectedStream, $text );
            return $this;
        }

        public function eol() {
            $this->write( PHP_EOL );
            $this->hadLineOutput = false;
            return $this;
        }

        public function writePadded( $text, $padding = 4, $paddingChar = " " ) {
            $this->write( str_repeat( $paddingChar, $padding ) );
            $this->write( $text );
            return $this;
        }

    }
