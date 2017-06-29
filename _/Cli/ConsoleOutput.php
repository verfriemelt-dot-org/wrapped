<?php

    namespace Wrapped\_\Cli;

    class ConsoleOutput {

        /**
         *
         * @param type $content
         * @return \Wrapped\_\Cli\ConsoleOutput
         */
        public function writeLn( $content = "" ) {
            echo $content . PHP_EOL;
            return $this;
        }

        /**
         *
         * @param type $content
         * @return \Wrapped\_\Cli\ConsoleOutput
         */
        public function write( $content ) {
            echo $content;
            return $this;
        }

        /**
         *
         * @return \Wrapped\_\Cli\ConsoleOutput
         */
        public function blankLine() {
            echo PHP_EOL;
            return $this;
        }

        public function writePadded( $content, $count, $padding = " " ) {
            echo str_repeat( $padding, $count ) . $content;
            return $this;
        }

        public function resetCursor() {
            echo "\r";
        }

    }
