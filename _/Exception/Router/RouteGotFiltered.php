<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Exception\Router;

    use \Wrapped\_\Http\Response\Response;

    class RouteGotFiltered
    extends RouterException {

        private $response;

        public function setResponse( Response $resposne ) {
            $this->response = $resposne;
        }

        public function hasReponse(): bool {
            return $this->response instanceof Response;
        }

        public function getReponse(): Response {
            return $this->response;
        }

    }
