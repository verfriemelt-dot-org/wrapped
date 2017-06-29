<?php

    namespace Wrapped\_\Controller;

    use \Wrapped\_\Controller\ControllerInterface;
    use \Wrapped\_\Exception\Router\RouterException;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;

    abstract class Controller
    implements ControllerInterface {

        public function handleRequest( Request $request ): Response {

            $methodName = $request->attributes()->get( 0, "index" );

            if ( !method_exists( $this, "handle_{$methodName}" ) || !is_callable( [ $this, "handle_{$methodName}" ] ) ) {
                throw new RouterException( "Method handle_{$methodName} is not callable on " . static::class );
            }

            $method = "handle_{$methodName}";

            return $this->{$method}($request);
        }

    }
