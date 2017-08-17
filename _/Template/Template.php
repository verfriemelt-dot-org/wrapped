<?php

    namespace Wrapped\_\Template;

    use \Wrapped\_\Output\Viewable;

    class Template {

        private $if                = [];
        private $vars              = [];
        private $repeater          = [];
        private $tokenChain;
        static private $chainCache = [];

        public function run() {

            $parser = (new TemplateParser() )
                ->setChain( $this->tokenChain )
                ->setData(
                [
                    "vars"     => $this->vars,
                    "if"       => $this->if,
                    "repeater" => $this->repeater
                ]
            );

            return $parser->parse();
        }

        public function yieldRun() {
            $parser = (new TemplateParser() )
                ->setChain( $this->tokenChain )
                ->setData(
                [
                    "vars"     => $this->vars,
                    "if"       => $this->if,
                    "repeater" => $this->repeater
                ]
            );

            foreach ( $parser->alternateParse() as $output ) {
                yield $output;
            }
        }

        /**
         *
         * @param type $input
         * @return Template
         */
        public function setRawTemplate( $input ) {
            $this->tokenChain = (new TemplateLexer() )->lex( $input )->getChain();
            return $this;
        }

        /**
         * loadfile
         * @return Template
         */
        public function parseFile( $path ) {

            if ( !isset( self::$chainCache[$path] ) ) {

                $fileContent = file_get_contents( $path );

                self::$chainCache[$path] = (new TemplateLexer() )->lex( $fileContent )->getChain();
            }

            $this->tokenChain = self::$chainCache[$path];
            return $this;
        }

        /**
         *
         * @param type $name
         * @return Repeater
         */
        public function createRepeater( $name ) {

            if ( !isset( $this->repeater[$name] ) ) {
                $this->repeater[$name] = new Repeater( $name );
            }

            return $this->repeater[$name];
        }

        /**
         *
         * @param string $name
         * @param bool $bool
         */
        public function setIf( $name, $bool = true ) {
            $this->if[$name] = new Ifelse( $name, $bool );
            return $this;
        }

        /**
         * set a variable to replace in the template
         * @param type $name name of variable
         * @param type $value value
         * @return static
         */
        public function set( $name, $value ) {

            if ( $value instanceof Viewable ) {
                $value = $value->getContents();
            }

            $this->vars[$name] = new Variable( $name, $value );
            return $this;
        }

        public function setArray( $array ) {

            if ( !is_array( $array ) ) {
                return false;
            }

            foreach ( $array AS $name => $value ) {
                $this->set( $name, $value );
            }

            return true;
        }

    }
