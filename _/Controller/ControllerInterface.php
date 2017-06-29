<?php

    namespace Wrapped\_\Controller;

    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;

    interface ControllerInterface {

        public function handleRequest( Request $request ): Response;
    }
