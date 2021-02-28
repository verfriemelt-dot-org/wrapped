<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Input;

    use \Wrapped\_\Session\Session;

    class CSRF {

        private $session = null;

        public function __construct( Session $session = null ) {
            $this->session = $session ?? Session::getInstance();
        }

        public function generateToken( string $contextName ): string {

            if ( $this->session->has( $contextName ) ) {
                return $this->session->get( $contextName );
            }

            $token = md5( uniqid( (string) rand(), true ) );
            $this->session->set( $contextName, $token );

            return $token;
        }

        public function validateToken( string $contextName, string $token ): bool {

            if ( $this->session->get( $contextName ) === $token ) {
                $this->session->delete( $contextName );
                return true;
            }

            return false;
        }

    }
