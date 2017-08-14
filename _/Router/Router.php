<?php

    namespace Wrapped\_\Router;

    use \Iterator;
    use \Wrapped\_\Exception\Router\NoRouteMatching;
    use \Wrapped\_\Exception\Router\NoRoutesPresent;
    use \Wrapped\_\Exception\Router\RouteGotFiltered;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;
    use \Wrapped\_\Singleton;

    class Router
    implements Iterator {

        use RouteIterator;
        use Singleton;

        private $request;
        private $basePath           = null;
        private $uri;
        private $routeAttributeData = [];
        private $globalFilter       = [];
        private $rawRouteHits       = [];

        private function __construct( Request $request = null ) {
            $this->setRequest( $request ?: Request::getInstance()  );
        }

        public function setRequest( Request $request ): Router {
            $this->request = $request;

            if ( php_sapi_name() == "cli" ) {
                $this->uri = $this->request->uri();
            } else {
                $this->uri = $this->request->pathInfo();
            }

            return $this;
        }

        /**
         * used for adding multiple routes
         * @param Routeable $routes
         * @return Router
         */
        public function addRoutes( Routable ... $routes ) {

            foreach ( $routes as $route ) {
                $this->routes[] = $route;
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

        private function isFiltered( $filterFunction ): bool {

            if ( !is_callable( $filterFunction ) ) {
                return false;
            }

            $functionResult = $filterFunction();

            if ( $functionResult === false ) {
                return false;
            }

            $filterException = new RouteGotFiltered( "route got filtered" );

            if ( $functionResult instanceof Response ) {
                $filterException->setResponse( $functionResult );
            }

            throw $filterException;
        }

        /**
         *
         * @return boolean|Route
         */
        public function run() {

            if ( empty( $this->routes ) ) {
                throw new NoRoutesPresent( "Router is empty" );
            }

            if ( !empty( $this->globalFilter ) ) {

                foreach ( $this->globalFilter as $filter ) {
                    $this->isFiltered( $filter );
                }
            }

            $this->stripBasePathFromRequest();
            $this->sortRoutes( $this->routes );

            $route = $this->findMatchingRoute( $this->uri, $this->routes );

            // nothing matching
            if ( $route === false ) {
                throw new NoRouteMatching( "Router has no matching routes for {$this->uri}" );
            }

            // setup attributes
            foreach ( $this->rawRouteHits as $hits ) {
                $this->routeAttributeData = array_merge( $this->routeAttributeData, $hits );
            }

            $this->request->setAttributes( $this->routeAttributeData );

            return $route;
        }

        public function findMatchingRoute( $uri, $routes ) {

            foreach ( $routes as $routeable ) {

                if ( preg_match( "~^{$routeable->getPath()}~", $uri, $routeHits ) ) {

                    // check for filter on routes and routeGroups
                    $this->isFiltered( $routeable->getFilterCallback() );

                    // this route is matching and were done
                    if ( $routeable instanceof Route ) {
                        // we store capturegroups in the attributes object of the request
                        $this->rawRouteHits[] = array_slice( $routeHits, 1 );

                        return $routeable;
                    }

                    // routegroup
                    // remove the current matching routepart
                    $routeUri = substr( $uri, mb_strlen( $routeHits[0] ) );

                    $routeGroupRoutes = $routeable->getRoutes();
                    $this->sortRoutes( $routeGroupRoutes );

                    // add attributse data from matching requests
                    $this->rawRouteHits[] = array_slice( $routeHits, 1 );

                    $result = $this->findMatchingRoute( $routeUri, $routeGroupRoutes );

                    if ( $result !== false ) {

                        // route, were done!
                        return $result;
                    } else {

                        // remove unmatched hits
                        array_pop( $this->rawRouteHits );
                    }
                }
            }

            return false;
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

        /**
         * runs a filter before matching any routes
         * @param callable $filter
         * @return $this
         */
        public function addGlobalFilter( callable $filter ) {
            $this->globalFilter[] = $filter;
            return $this;
        }

    }
