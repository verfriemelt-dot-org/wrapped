<?php

    namespace Wrapped\_\Controller;

    use \Wrapped\_\Controller\ControllerInterface;
    use \Wrapped\_\Exception\Router\RouterException;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;

    abstract class Controller
    implements ControllerInterface {

        public function handleRequest( Request $request ): Response {

            // used to filter out named regexp hits
            $attributes_on_ints = [];
            $attributes_on_strings = [];

            foreach( $request->attributes()->all() as $index => $value ) {
                if ( is_integer( $index ) ) {
                    $attributes_on_ints[] = $value;
                } else {
                    $attributes_on_strings[] = $value;
                }
            }

            $int_only_references = array_diff( $attributes_on_ints , $attributes_on_strings );

            $methodName = current( $int_only_references) ?: 'index'; // $request->attributes()->get( 0, "index" );

            if ( !method_exists( $this, "handle_{$methodName}" ) || !is_callable( [ $this, "handle_{$methodName}" ] ) ) {
                throw new RouterException( "Method handle_{$methodName} is not callable on " . static::class );
            }

            $method = "handle_{$methodName}";

            return $this->{$method}($request);
        }

    }
