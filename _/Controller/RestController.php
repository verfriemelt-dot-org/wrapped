<?php

    namespace Wrapped\_\Controller;

    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;

    abstract class RestController
    implements ControllerInterface {

        public function handleRequest( Request $request ): Response {

            $verb = $request->requestMethod();

            switch ( $verb ) {
                case "GET" : return $this->get( $request );
                    break;
                case "PUT" : return $this->put( $request );
                    break;
                case "POST" : return $this->post( $request );
                    break;
                case "DELETE" : return $this->delete( $request );
                    break;
                default : {
                    if ( method_exists( $this, $this->requestType ) && is_callable( [ $this, $this->requestType ] ) ) {
                        return $this->{$this->requestType}( $request );
                    }
                }
            }
        }

        abstract public function get( Request $request ): Response;

        abstract public function put( Request $request ): Response;

        abstract public function post( Request $request ): Response;

        abstract public function delete( Request $request ): Response;
    }
