<?php

    namespace verfriemelt\wrapped\_\Events;

    use \Throwable;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Http\Response\Response;

    class ExceptionEvent
    implements EventInterface {

        protected Throwable $throwable;

        protected Request $request;

        protected Response $response;

        public function __construct( Throwable $throwable, Request $request ) {
            $this->request   = $request;
            $this->throwable = $throwable;
        }

        public function setResponse( Response $response ): static {
            $this->response = $response;
            return $this;
        }

        public function getThrowable(): Throwable {
            return $this->throwable;
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
