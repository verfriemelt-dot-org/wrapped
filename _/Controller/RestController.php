<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Controller;

    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Http\Response\Http;
    use \verfriemelt\wrapped\_\Http\Response\Response;

    abstract class RestController
    implements ControllerInterface {

        protected $supportedVerbs = [
            "GET",
            "POST",
            "DELETE",
            "PUT",
            "PATCH"
        ];

        public function handleRequest( Request $request ): Response {

            $verb = $request->requestMethod();

            if (
                in_array( $verb, $this->supportedVerbs ) &&
                method_exists( $this, $verb ) &&
                is_callable( [ $this, $verb ] )
            ) {
                return $this->{$verb}( $request );
            }

            return (new Response( Http::NOT_IMPLEMENTED ));
        }

    }
