<?php

    namespace Wrapped\_\Http\Response;

    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Router\Router;

    class Redirect
    extends \Wrapped\_\Http\Response\Response {

        private $to;
        private $ignoreBasePath = false;

        public function __construct( $path = null ) {
            $this->to = $path;
            $this->temporarily();
        }

        public function send() {

            if ( $this->ignoreBasePath === false ) {
                $path = Router::getInstance()->getBasePath() . $this->to;
            } else {
                $path = $this->to;
            }

            $this->addHeader( new \Wrapped\_\Http\Response\HttpHeader( "Location", $path ) );
            parent::send();
        }

        /**
         * returns http 301
         * @return \Wrapped\_\Response\Redirect
         */
        public function permanent() {
            $this->setStatusCode( \Wrapped\_\Http\Response\Http::MOVED_PERMANENTLY );
            return $this;
        }

        /**
         *
         * @return \Wrapped\_\Response\Redirect
         */
        public function temporarily() {
            $this->setStatusCode( \Wrapped\_\Http\Response\Http::TEMPORARY_REDIRECT );
            return $this;
        }

        public function seeOther( $to ) {
            $this->setStatusCode( \Wrapped\_\Http\Response\Http::SEE_OTHER );
            $this->to = $to;
            return $this;
        }

        static public function to( $path ) {
            return new static( $path );
        }

        public function ignoreBasePath( $bool = true ) {
            $this->ignoreBasePath = $bool;
            return $this;
        }

        static public function reload() {
            return (new static )->ignoreBasePath()->seeOther( Request::getInstance()->uri() );
        }

    }
