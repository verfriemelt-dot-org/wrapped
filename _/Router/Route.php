<?php

    namespace Wrapped\_\Router;

    use \Wrapped\_\Controller\ControllerInterface;
    use \Wrapped\_\Exception\Router\IllegalCallbackSpecified;
    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Http\Response\Response;

    class Route
    implements Routable {

        private $path;
        private $callback;
        private $filter;
        private $priority = 100;

        /**
         * create new Route for chaining
         * @param type $path
         * @return \static
         */
        public static function create( $path ) {
            return new static( $path );
        }

        public function __construct( $path ) {
            $this->setPath( $path );
        }

        public function getPath() {
            return $this->path;
        }

        public function getPriority() {
            return $this->priority;
        }

        /**
         *
         * @param type $prio
         * @return Route
         */
        public function setPriority( $prio ) {
            $this->priority = $prio;
            return $this;
        }

        public function getCallback() {
            return $this->callback;
        }

        /**
         * return filter for checking
         * @return callable
         */
        public function getFilterCallback() {
            return $this->filter;
        }

        /**
         *
         * @param type $path
         * @return \Wrapped\_\Route
         */
        public function setPath( $path ) {
            $this->path = $path;
            return $this;
        }

        /**
         *
         * @param \Wrapped\_\callable $callback
         * @return \Wrapped\_\Route
         */
        public function call( $callback ) {

            $this->callback = $callback;
            return $this;
        }

        /**
         * if this function returns true no callback is triggered
         * @param type $filter
         * @return \Wrapped\_\Route
         */
        public function setFilterCallback( callable $filter ) {
            $this->filter = $filter;
            return $this;
        }

        public function checkFilter() {
            if ( is_callable( $this->filter ) && call_user_func( $this->filter ) === true ) {
                return false;
            }

            return true;
        }

        public function isValidCallback() {

            if ( is_callable( $this->callback ) ||
                class_implements( $this->callback, ControllerInterface::class ) ||
                $this->callback instanceof Response ) {

                return true;
            }

            throw new IllegalCallbackSpecified(
            "invalid callback \"{$this->callback}\""
            );
        }

        /**
         *
         * @return Response|boolean
         */
        public function runCallback( Request $request ) {

            $this->isValidCallback();

            if ( !$this->checkFilter() ) {
                return false;
            }

            // if plain reponse as callback, just return the reponse
            if ( $this->callback instanceof Response ) {
                return $this->callback;
            }

            if ( is_callable( $this->callback ) ) {
                return call_user_func( $this->callback, $request );
            }

            return (new $this->callback() )->handleRequest( $request );
        }

    }
