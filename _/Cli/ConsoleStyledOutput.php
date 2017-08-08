<?php

    namespace Wrapped\_\Cli;

    class ConsoleStyledOutput
    extends ConsoleOutput {

        const NONE   = 0;
        const BLACK  = 30;
        const RED    = 31;
        const GREEN  = 32;
        const YELLOW = 33;
        const BLUE   = 34;
        const PURPLE = 35;
        const CYAN   = 36;
        const WHITE  = 37;
        const REGULAR   = "0";
        const BOLD      = "1";
        const UNDERLINE = "4";

        private $fgColor = null;
        private $bgColor = null;
        private $style   = "0";

        public function __construct() {

            $that = $this;
        }

        public function setFgColor( $color = null ) {
            $this->fgColor = $color;
            $this->setStyle();
            return $this;
        }

        public function setBgColor( $color = null ) {
            $this->bgColor = $color + 10;
            $this->setStyle();
            return $this;
        }

        public function setFontStyle( $style = null ) {
            $this->style = $style;
            $this->setStyle();

            return $this;
        }

        private function setStyle() {
            if ( $this->fgColor !== null )
                $this->write( "\e[{$this->style};{$this->fgColor}m" );
            if ( $this->bgColor !== null )
                $this->write( "\e[{$this->bgColor}m" );
        }

        public function __destruct() {
            $this->setBgColor( static::NONE );
            $this->setFgColor( static::NONE );
        }

    }
