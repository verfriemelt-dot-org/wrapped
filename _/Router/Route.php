<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Router;

    use \verfriemelt\wrapped\_\Controller\ControllerInterface;
    use \verfriemelt\wrapped\_\Exception\Router\IllegalCallbackSpecified;
    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Http\Response\Response;

    class Route
    implements Routable {

        private string $path;

        private $callback;

        private $filter;

        private int $priority = 100;

        public static function create( string $path ): static {
            return new self( $path );
        }

        public function __construct( string $path ) {
            $this->setPath( $path );
        }

        public function getPath(): string {
            return $this->path;
        }

        public function getPriority(): int {
            return $this->priority;
        }

        public function setPriority( int $prio ): static {
            $this->priority = $prio;
            return $this;
        }

        public function getCallback(): callable {
            return $this->callback;
        }

        public function getFilterCallback(): ?callable {
            return $this->filter;
        }

        public function setPath( $path ) {
            $this->path = $path;
            return $this;
        }

        public function call( callable | Response | string $callback ): static {
            $this->callback = $callback;
            return $this;
        }

        /**
         * if this function returns true no callback is triggered
         * @param callable $filter
         * @return static
         */
        public function setFilterCallback( callable $filter ): static {
            $this->filter = $filter;
            return $this;
        }

        public function checkFilter(): bool {
            if ( is_callable( $this->filter ) && call_user_func( $this->filter ) === true ) {
                return false;
            }

            return true;
        }

        public function isValidCallback(): bool {

            if ( is_callable( $this->callback ) ||
                in_array( ControllerInterface::class, class_implements( $this->callback ) ) ||
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
