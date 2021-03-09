<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Http\Response;

    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Router\Router;

    class Redirect
    extends Response {

        private $to;

        private $ignoreBasePath = false;

        public function __construct( $path = null ) {
            $this->to = $path;
            $this->temporarily();
        }

        public function send(): Response {

            if ( $this->ignoreBasePath === false ) {
                $path = Router::getInstance()->getBasePath() . $this->to;
            } else {
                $path = $this->to;
            }

            $this->addHeader( new HttpHeader( "Location", $path ) );
            return parent::send();
        }

        /**
         * returns http 301
         * @return \verfriemelt\wrapped\_\Response\Redirect
         */
        public function permanent(): Redirect {
            $this->setStatusCode( Http::MOVED_PERMANENTLY );
            return $this;
        }

        /**
         *
         * @return \verfriemelt\wrapped\_\Response\Redirect
         */
        public function temporarily(): Redirect {
            $this->setStatusCode( Http::TEMPORARY_REDIRECT );
            return $this;
        }

        public function seeOther( $to ): Redirect {
            $this->setStatusCode( Http::SEE_OTHER );
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
