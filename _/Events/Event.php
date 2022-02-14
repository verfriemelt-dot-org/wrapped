<?php

    namespace verfriemelt\wrapped\_\Events;

    use \Throwable;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Http\Response\Response;

    class Event
        implements EventInterface {

        protected Request $request;

        protected Response $response;

        public function __construct( Request $request ) {
            $this->request   = $request;
        }

        public function setResponse( Response $response ): static {
            $this->response = $response;
            return $this;
        }

        public function getRequest(): Request {
            return $this->request;
        }

        public function hasResponse(): bool {
            return isset( $this->response );
        }

        public function getResponse(): Response {
            return $this->response;
        }

    }
