<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Template;

    use \RuntimeException;

    class Template {

        private $if = [];

        private $vars = [];

        private $repeater = [];

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

        public function setRawTemplate( string $input ): static {
            $this->tokenChain = (new TemplateLexer() )->lex( $input )->getChain();
            return $this;
        }

        public function parseFile( string $path ): static {

            if ( !isset( self::$chainCache[$path] ) ) {

                $fileContent = file_get_contents( $path );

                if ( $fileContent === false ) {
                    throw new RuntimeException( "cannot load template at {$path}" );
                }

                self::$chainCache[$path] = (new TemplateLexer() )->lex( $fileContent )->getChain();
            }

            $this->tokenChain = self::$chainCache[$path];
            return $this;
        }

        public function createRepeater( string $name ): Repeater {

            if ( !isset( $this->repeater[$name] ) ) {
                $this->repeater[$name] = new Repeater( $name );
            }

            return $this->repeater[$name];
        }

        public function mapToRepeater( string $name, string $variable, $data ): Repeater {

            $r = $this->createRepeater( $name );
            array_map( fn( $i ) => $r->set( $variable, $i )->save(), $data );

            return $r;
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

        public function set( string $name, mixed $value ): static {
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
