<?php

    namespace Wrapped\_\Template;

    use \Exception;
    use \Wrapped\_\Template\Token\T_IfClose;
    use \Wrapped\_\Template\Token\T_IfElse;
    use \Wrapped\_\Template\Token\T_IfOpen;
    use \Wrapped\_\Template\Token\T_RepeaterClose;
    use \Wrapped\_\Template\Token\T_RepeaterOpen;
    use \Wrapped\_\Template\Token\T_String;
    use \Wrapped\_\Template\Token\T_Variable;
    use \Wrapped\_\Template\Token\Token;

    class TemplateLexer {

        private $input       = "";
        private $inputLength = 0;

        /**
         *
         * @var \Wrapped\_\Template\Token
         */
        private $tokenChain;

        /**
         *
         * @var \Wrapped\_\Template\Token
         */
        private $currentToken;
        private $currentPos   = 0;
        private $currentState = 1;

        // 0 = finished
        // 1 = Open CurlySuchen
        // 2 = CurlyOpenFound -> IF, ELSE, Repeater, Variable -> Closing

        public function setTokenChain( Token $chain ) {
            $this->tokenChain = $chain;
            return $this;
        }

        /**
         *
         * @param type $input
         * @return TemplateLexer
         */
        public function lex( $input ) {
            $this->input       = $input;
            $this->inputLength = strlen( $this->input );

            if ( $this->inputLength > 0 ) {
                return $this->workon();
            }

            $this->tokenChain = new T_String();

            return $this;
        }

        public function workon() {

            while ( $this->currentPos < $this->inputLength ) {
                switch ( $this->currentState ) {
                    case 0 :
                        return true;
                    case 1 : $this->findOpenCurly();
                        break;
                    case 2 : $this->findCurlyContent();
                        break;
                }
            }

            return $this;
        }

        private function findCurlyContent() {

            $closingCurlyPos = strpos( $this->input, "}}", $this->currentPos );

            if ( $closingCurlyPos === false ) {
                new Exception( "closing curly missing" );
                return;
            }

            $contentBetweenCurlyBraces = substr( $this->input, $this->currentPos, $closingCurlyPos - $this->currentPos );
            $hit                       = false;

            if ( empty( trim( $contentBetweenCurlyBraces ) ) ) {
                $this->currentState = 1;
                $this->currentPos   = $closingCurlyPos + 2;
                return false;
            }

            if ( preg_match( "~^ ?(?<negate>!)?(?<close>/)?(?<type>if|else)=['\"](?<name>[a-zA-Z0-9-_]+)['\"] ?$~", $contentBetweenCurlyBraces, $pregHit ) ) {

                $token                 = $pregHit["type"] === "else" ? new T_IfElse : ($pregHit["close"] === "" ? new T_IfOpen() : new T_IfClose );
                $token->negated        = $pregHit["negate"] !== "";
                $token->currentContent = $pregHit["name"];
                $hit                   = true;
            }

            if ( !$hit && preg_match( "~^ ?(?<close>/)?repeater=['\"](?<name>[a-zA-Z0-9-_]+)['\"] ?$~", $contentBetweenCurlyBraces, $pregHit ) ) {

                $token                 = $pregHit["close"] === "" ? new T_RepeaterOpen() : new T_RepeaterClose;
                $token->currentContent = $pregHit["name"];

                $this->appendToChain( $token );
            }

            if ( !$hit && preg_match( "~ ?(?<doNotEscape>!)? ?(?<name>[0-9a-zA-Z-_]+)\|?(?<format>[a-zA-Z0-9-_]+)? ?~", $contentBetweenCurlyBraces, $pregHit ) ) {

                // rest variable
                $token                 = new T_Variable();
                $token->currentContent = $pregHit["name"];
                $token->escape         = $pregHit["doNotEscape"] !== "!";

                if ( isset( $pregHit["format"] ) ) {
                    $token->formatCallback = $pregHit["format"];
                }
            }

            $this->appendToChain( $token );

            $this->currentPos   = $closingCurlyPos + 2;
            $this->currentState = 1;

            return;
        }

        private function findOpenCurly() {

            $pos = strpos( $this->input, "{{", $this->currentPos );

            if ( $pos === false ) {

                $content               = substr( $this->input, $this->currentPos );
                $token                 = new T_String();
                $token->currentContent = $content;
                $this->currentPos      += strlen( $content );
            } else {

                // alles davor is string
                $token                 = new T_String();
                $token->currentContent = substr( $this->input, $this->currentPos, $pos - $this->currentPos );

                $this->currentState = 2;
                $this->currentPos   = $pos + 2;
            }

            // zur chain
            $this->appendToChain( $token );
        }

        private function appendToChain( \Wrapped\_\Template\Token\Token $token ) {

            if ( $this->tokenChain === null ) {
                $this->tokenChain = $token;
            } else {
                $this->currentToken->nextToken = $token;
            }

            $this->currentToken = $token;
        }

        public function printChainInline( $currentToken = null ) {

            $currentToken = $currentToken ?: $this->tokenChain;

            echo $currentToken->getTokenName() . "( " . $currentToken->currentContent . " )->";

            if ( $currentToken->nextToken !== null ) {
                $this->printChainInline( $currentToken->nextToken );
            }
        }

        public function printChain( $currentToken = null ) {

            $currentToken = $currentToken ?: $this->tokenChain;

            echo $currentToken->getTokenName() . " »" . $currentToken->currentContent . "«" . PHP_EOL;

            if ( $currentToken->nextToken !== null ) {
                $this->printChain( $currentToken->nextToken );
            }
        }

        /**
         *
         * @return Token
         */
        public function getChain() {
            return $this->tokenChain;
        }

    }
