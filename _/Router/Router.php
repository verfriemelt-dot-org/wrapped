<?php

    namespace Wrapped\_\Router;

    use \Iterator;
    use \Wrapped\_\Exception\Router\NoRouteMatching;
    use \Wrapped\_\Exception\Router\NoRoutesPresent;
    use \Wrapped\_\Exception\Router\RouteGotFiltered;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Singleton;

    class Router
    implements Iterator {

        use RouteIterator;
        use Singleton;

        private $request;
        private $basePath           = null;
        private $uri;
        private $routeAttributeData = [];

        private function __construct( Request $request = null ) {

            $this->request = $request ?: Request::getInstance();

            if ( php_sapi_name() == "cli" ) {
                $this->uri = $this->request->uri();
            } else {
                $this->uri = $this->request->pathInfo();
            }
        }

        /**
         *
         * @param Routable $route
         * @return Router
         */
        public function addRoute( Routable $route ) {
            $this->routes[] = $route;
            return $this;
        }

        /**
         * used for adding multiple routes
         * @param Routeable $routes
         * @return Router
         */
        public function addRoutes( Routable ... $routes ) {

            foreach ( $routes as $route ) {
                $this->addRoute( $route );
            }

            return $this;
        }

        /**
         * to strip subpath like "/www/"
         * @param type $path
         * @return $this
         */
        public function setBasePath( $path ) {
            $this->basePath = $path;
            return $this;
        }

        /**
         * returns current basePath
         * @return string
         */
        public function getBasePath() {
            return $this->basePath;
        }

        /**
         *
         * @return boolean|Route
         */
        public function run() {

            if ( empty( $this->routes ) ) {
                throw new NoRoutesPresent( "Router is empty" );
            }

            $this->stripBasePathFromRequest();
            $this->sortRoutes( $this->routes );

            $route = $this->findMatchingRoute( $this->uri, $this->routes );
            $this->request->setAttributes( $this->routeAttributeData );

            return $route;
        }

        public function findMatchingRoute( $uri, $routes ) {

            foreach ( $routes as $routeable ) {

                $routePath = $routeable->getPath();

                if ( preg_match( "~^{$routePath}~", $uri, $routeHits ) ) {

                    // check for filter
                    if ( $routeable->getFilterCallback() !== null && call_user_func( $routeable->getFilterCallback() ) ) {
                        throw new RouteGotFiltered( "route got filtered" );
                    }

                    // we store capturegroups in the attributes object of the request
                    $this->routeAttributeData = array_merge( $this->routeAttributeData, array_slice( $routeHits, 1 ) );

                    if ( $routeable instanceof RouteGroup ) {

                        // remove the current matching routepart
                        $uri = substr( $uri, mb_strlen( $routeHits[0] ) );
                        return $this->findMatchingRoute( $uri, $routeable->getRoutes() );
                    }

                    // route, were done!
                    return $routeable;
                }
            }

            // nothing matching
            throw new NoRouteMatching( "Router has no matching routes for {$this->uri}" );
        }

        /**
         * sort routes according to priority
         */
        private function sortRoutes( &$routes ) {
            usort( $routes, function ( Routable $a, Routable $b ) {
                return $a->getPriority() <=> $b->getPriority();
            } );
        }

        /**
         * removes base path set in router and strips /index.php/admin routing
         * so that we get only /admin or / in case of just hitting /index.php
         */
        private function stripBasePathFromRequest() {

            if ( $this->basePath !== null ) {
                $this->uri = substr( $this->uri, strlen( $this->basePath ) );
            }
        }

        public function destroy() {
            static::$handle = null;
        }

    }
