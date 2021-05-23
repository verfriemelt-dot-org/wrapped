<?php

    namespace verfriemelt\wrapped\_;

    use \_\Controller\PageNotFoundController;
    use \Closure;
    use \Exception;
    use \verfriemelt\wrapped\_\DI\ArgumentMetadataFactory;
    use \verfriemelt\wrapped\_\DI\ArgumentResolver;
    use \verfriemelt\wrapped\_\DI\Container;
    use \verfriemelt\wrapped\_\Exception\Router\NoRouteMatching;
    use \verfriemelt\wrapped\_\Exception\Router\NoRoutesPresent;
    use \verfriemelt\wrapped\_\Exception\Router\RouteGotFiltered;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Http\Response\Redirect;
    use \verfriemelt\wrapped\_\Http\Response\Response;
    use \verfriemelt\wrapped\_\Router\Router;

    class Kernel {

        protected Router $router;

        protected Request $request;

        protected Container $container;

        public function __construct() {

            $this->request = Request::createFromGlobals();

            $this->container = new Container();
            $this->container->register( $this->request::class, $this->request );

            $this->router = $this->container->get( Router::class );
        }

        public function getContainer(): Container {
            return $this->container;
        }

        public function addAutoloadPath( string $path ): static {

            spl_autoload_register( function ( $class ) use ( $path ) {

                $possiblePath = $path . "/" . str_replace( "\\", "/", $class ) . ".php";

                if ( file_exists( $possiblePath ) ) {
                    return require_once $possiblePath;
                }
            } );

            return $this;
        }

        public function loadSetup( string $path ): static {

            foreach ( include_once $path as $func ) {
                $func( $this->request, $this->container );
            }

            return $this;
        }

        public function containerConfiguration( Closure $config ): static {
            $config( $this->container );
            return $this;
        }

        public function loadRoutes( string $path ): static {

            $this->router->addRoutes( ... require_once $path );
            return $this;
        }

        public function handle(): ?Response {

            try {

                $route    = $this->router->handleRequest( $this->request );
                $callback = $route->getCallback();

                if ( !$route->checkFilter() ) {
                    throw new RouteGotFiltered();
                }

                if ( $callback instanceof Response ) {
                    $response = $callback;
                } elseif ( $callback instanceof Closure ) {

                    $resolver  = new ArgumentResolver( $this->container, new ArgumentMetadataFactory );
                    $arguments = $resolver->resolv( $callback );
                    $response  = $callback( ...$arguments );
                } else {

                    $resolver = new ArgumentResolver( $this->container, new ArgumentMetadataFactory );

                    $constructorArgument = $resolver->resolv( $callback );
                    $methodArguments     = $resolver->resolv( $callback, 'handleRequest' );

                    $response = (new $callback( ... $constructorArgument ) )
                        ->setContainer( $this->container )
                        ->prepare()
                        ->handleRequest( ...$methodArguments );
                }

                return $response;
            } catch ( NoRouteMatching $e ) {
                $res = $this->container->get( PageNotFoundController::class )->handle_404( $request );
            } catch ( NoRoutesPresent $e ) {
                $res = new Response();
                $res->setStatusCode( 404 );
                $res->setContent( "404 - no routes" );
            } catch ( RouteGotFiltered $e ) {
                $res = new Redirect( "/login/auth" );
                $res->setStatusCode( 307 );
                $res->setContent( "forbidden" );
            }

            return $res;
        }

    }
