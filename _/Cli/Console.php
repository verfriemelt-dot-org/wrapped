<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Cli;

    use \Wrapped\_\Http\ParameterBag;

    class Console {

        const STYLE_NONE = 0;

        const STYLE_BLACK = 30;

        const STYLE_RED = 31;

        const STYLE_GREEN = 32;

        const STYLE_YELLOW = 33;

        const STYLE_BLUE = 34;

        const STYLE_PURPLE = 35;

        const STYLE_CYAN = 36;

        const STYLE_WHITE = 37;

        const STYLE_REGULAR = "0";

        const STYLE_BOLD = "1";

        const STYLE_UNDERLINE = "4";

        protected $currentFgColor = SELF::STYLE_NONE;

        protected $currentBgColor = SELF::STYLE_NONE;

        protected $currentFontStyle = SELF::STYLE_REGULAR;

        protected $selectedStream;

        protected $stdout = STDOUT;

        protected $stderr = STDERR;

        protected $linePrefixFunc;

        protected $hadLineOutput = false;

        protected $dimensions = null;

        protected $inTerminal = false;

        protected $colorSupported = null;

        protected $forceColor = false;

        /**
         *
         * @var ParameterBag
         */
        protected $argv;

        public static function getInstance(): Console {
            return new static( isset( $_SERVER["argv"] ) ? $_SERVER["argv"] : [] );
        }

        public function __construct() {

            $this->selectedStream = &$this->stdout;
            $this->argv           = new ParameterBag( $_SERVER["argv"] ?? [] );

            $this->inTerminal = isset( $_SERVER['TERM'] );
        }

        public static function isCli(): bool {
            return php_sapi_name() === "cli";
        }

        public function getArgv(): ParameterBag {
            return $this->argv;
        }

        public function getArgvAsString(): string {

            // omit first element
            return implode( " ", $this->argv->except( [ 0 ] ) );
        }

        public function setPrefixCallback( callable $func ): Console {
            $this->linePrefixFunc = $func;
            return $this;
        }

        public function toSTDOUT(): Console {
            $this->selectedStream = &$this->stdout;
            return $this;
        }

        public function toSTDERR(): Console {
            $this->selectedStream = &$this->stderr;
            return $this;
        }

        public function write( $text, $color = null ): Console {

            if ( $color !== null ) {
                $this->setForegroundColor( $color );
            }

            if ( $this->linePrefixFunc !== null && $this->hadLineOutput !== true ) {
                fwrite( $this->selectedStream, ($this->linePrefixFunc)() );
                $this->hadLineOutput = true;
            }

            fwrite( $this->selectedStream, $text );

            if ( $color !== null ) {
                $this->setForegroundColor( static::STYLE_NONE );
            }

            return $this;
        }

        public function writeLn( $text, $color = null ): Console {
            return $this->write( $text, $color )->eol();
        }

        public function cr(): Console {
            $this->write( "\r" );
            return $this;
        }

        public function eol(): Console {
            $this->write( PHP_EOL );
            $this->hadLineOutput = false;
            return $this;
        }

        public function writePadded( $text, $padding = 4, $paddingChar = " ", $color = null ): Console {
            $this->write( str_repeat( $paddingChar, $padding ) );
            $this->write( $text, $color );

            return $this;
        }

        // this is blocking
        public function read() {
            return fgets( STDIN );
        }

        protected function setOutputStyling() {

            if ( $this->colorSupported === null ) {
                $this->colorSupported = $this->supportsColor();
            }

            if ( !$this->colorSupported ) {
                return;
            }

            $this->write( "\e[{$this->currentFontStyle};{$this->currentFgColor}m" );

            if ( $this->currentBgColor !== self::STYLE_NONE ) {
                $this->write( "\e[{$this->currentBgColor}m" );
            }
        }

        public function setFontFeature( int $style ): Console {
            $this->currentFontStyle = $style;
            $this->setOutputStyling();
            return $this;
        }

        public function setBackgroundColor( int $color ): Console {
            $this->currentBgColor = $color + 10;
            $this->setOutputStyling();
            return $this;
        }

        public function setForegroundColor( int $color ): Console {
            $this->currentFgColor = $color;
            $this->setOutputStyling();
            return $this;
        }

        public function cls(): Console {
            $this->write( "\e[2J" );
            return $this;
        }

        public function up( int $amount = 1 ): Console {
            $this->write( "\e[{$amount}A" );
            return $this;
        }

        public function down( int $amount = 1 ): Console {
            $this->write( "\e[{$amount}B" );
            return $this;
        }

        public function right( int $amount = 1 ): Console {
            $this->write( "\e[{$amount}C" );
            return $this;
        }

        public function left( int $amount = 1 ): Console {
            $this->write( "\e[{$amount}D" );
            return $this;
        }

        /**
         * stores cursor position
         * @return \Wrapped\_\Cli\Console
         */
        public function push(): Console {
            $this->write( "\e[s" );
            return $this;
        }

        /**
         * restores cursor position
         * @return \Wrapped\_\Cli\Console
         */
        public function pop(): Console {
            $this->write( "\e[u" );
            return $this;
        }

        public function jump( int $x = 0, int $y = 0 ): Console {
            $this->write( "\e[{$y};{$x}f" );
            return $this;
        }

        /**
         * reset all style features
         */
        public function __destruct() {
            if ( $this->currentFgColor !== self::STYLE_NONE || $this->currentBgColor !== self::STYLE_NONE ) {
                $this->write( "\e[0m" );
            }
        }

        public function getWidth(): ?int {

            if ( $this->dimensions === null ) {
                $this->updateDimensions();
            }

            return $this->dimensions[0] ?? null;
        }

        public function getHeight(): ?int {

            if ( $this->dimensions === null ) {
                $this->updateDimensions();
            }

            return $this->dimensions[1] ?? null;
        }

        public function updateDimensions(): bool {

            $descriptorspec = [
                1 => [ 'pipe', 'w' ],
                2 => [ 'pipe', 'w' ],
            ];

            $process = proc_open( 'stty -a | grep columns', $descriptorspec, $pipes, null, null, [ 'suppress_errors' => true ] );

            if ( is_resource( $process ) ) {
                $info = stream_get_contents( $pipes[1] );
                fclose( $pipes[1] );
                fclose( $pipes[2] );
                proc_close( $process );
            } else {
                return false;
            }

            if ( preg_match( '/rows.(\d+);.columns.(\d+);/i', $info, $matches ) ) {
                // extract [w, h] from "rows h; columns w;"
                $this->dimensions[0] = (int) $matches[2];
                $this->dimensions[1] = (int) $matches[1];
            } elseif ( preg_match( '/;.(\d+).rows;.(\d+).columns/i', $info, $matches ) ) {
                // extract [w, h] from "; h rows; w columns"
                $this->dimensions[0] = (int) $matches[2];
                $this->dimensions[1] = (int) $matches[1];
            } else {
                return false;
            }

            return true;
        }

        public function forceColorOutput( $bool = true ) {
            $this->forceColor = $bool;
            return $this;
        }

        public function supportsColor(): bool {

            if ( $this->forceColor ) {
                return true;
            }

            if ( !$this->inTerminal ) {
                return false;
            }

            $descriptorspec = [
                1 => [ 'pipe', 'w' ],
                2 => [ 'pipe', 'w' ],
            ];

            $process = proc_open( 'tput colors', $descriptorspec, $pipes, null, null, [ 'suppress_errors' => true ] );

            if ( is_resource( $process ) ) {
                $info = stream_get_contents( $pipes[1] );
                fclose( $pipes[1] );
                fclose( $pipes[2] );
                proc_close( $process );
            } else {
                return false;
            }

            return (int) $info > 1;
        }

    }
