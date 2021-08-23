<?php

    namespace verfriemelt\wrapped\_\Router;

    use \RuntimeException;
    use \verfriemelt\wrapped\_\Http\Response\Redirect;

    class RedirectException
    extends RuntimeException {

        protected Redirect $redirect;

        public function __construct( Redirect $redirect ) {

            $this->redirect = $redirect;
        }

        public function getRedirect(): Redirect {
            return $this->redirect;
        }

    }
