<?php namespace Wrapped\_\Template\Token;

    abstract class Token {

        public $currentContent = '';
        public $currentLength = 0;

        public $priority = 10;
        public $maxLength = 0;

        /** @var Token */
        public $nextToken = null;
        public $prevToken = null;

        private $line;
        private $linePos;
        private $pos;

        abstract public function getTokenName();

        /**
         *
         * @param \core\core\ParserEngine\Token $prev
         * @return \core\core\ParserEngine\Token
         */
        public function setPrevToken( Token $prev ) {
            $this->prevToken = $prev;
            $this->prevToken->nextToken = $this;
            return $this;
        }

        /**
         *
         * @param \core\core\ParserEngine\Token $next
         * @return \core\core\ParserEngine\Token
         */
        public function setNextToken( Token $next = null ) {
            $this->nextToken = $next;
            $this->nextToken->prevToken = $this;
            return $this;
        }

        /**
         *
         * @return string
         */
        public function getContent() {
            return $this->currentContent;
        }

        public function getLine() {
            return $this->line;
        }

        public function getLinePos() {
            return $this->linePos;
        }

        public function getPos() {
            return $this->pos;
        }

        /**
         *
         * @param type $line
         * @return \core\core\ParserEngine\Token
         */
        public function setLine( $line ) {
            $this->line = $line;
            return $this;
        }

        /**
         *
         * @param type $linePos
         * @return \core\core\ParserEngine\Token
         */
        public function setLinePos( $linePos ) {
            $this->linePos = $linePos;
            return $this;
        }

        /**
         *
         * @param type $pos
         * @return \core\core\ParserEngine\Token
         */
        public function setPos( $pos ) {
            $this->pos = $pos;
            return $this;
        }
    }
