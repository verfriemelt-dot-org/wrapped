<?php

    namespace Wrapped\_\Template\Token;

    abstract class Token {

        public $currentContent = '';
        public $currentLength  = 0;
        public $priority       = 10;
        public $maxLength      = 0;

        /** @var Token */
        public $nextToken = null;
        public $prevToken = null;
        private $line;
        private $linePos;
        private $pos;

        abstract public function getTokenName();

        /**
         *
         * @param Token $prev
         * @return $this
         */
        public function setPrevToken( Token $prev ) {
            $this->prevToken            = $prev;
            $this->prevToken->nextToken = $this;
            return $this;
        }

        /**
         *
         * @param Token $next
         * @return $this
         */
        public function setNextToken( Token $next = null ) {
            $this->nextToken            = $next;
            $this->nextToken->prevToken = $this;
            return $this;
        }

        /**
         *
         * @return string
         */
        public function getContent(): string {
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
         * @return $this
         */
        public function setLine( $line ) {
            $this->line = $line;
            return $this;
        }

        /**
         *
         * @param type $linePos
         * @return $this
         */
        public function setLinePos( $linePos ) {
            $this->linePos = $linePos;
            return $this;
        }

        /**
         *
         * @param type $pos
         * @return $this
         */
        public function setPos( $pos ) {
            $this->pos = $pos;
            return $this;
        }

    }
