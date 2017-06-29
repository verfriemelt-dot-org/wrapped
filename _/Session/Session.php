<?php

    namespace Wrapped\_\Session;

    use \Wrapped\_\Http\Request\Request;

    class Session
    implements SessionHandler {

        use \Wrapped\_\Singleton;

        CONST SESSION_COOKIE_NAME = "scId";
        CONST SESSION_TIMEOUT     = 60 * 60 * 24 * 7;

        private $dataObj     = null;
        private $sessionId   = null;
        private $storageObj  = null;
        private $currentData = [];

        private function __construct( $sessionStorage = null ) {

            if ( $sessionStorage === null || class_implements( $sessionStorage, SessionDataObject::class ) ) {
                $this->storageObj = SessionMysql::class;
            } else {
                $this->storageObj = $sessionStorage;
            }

            if ( Request::getInstance()->cookies()->has( self::SESSION_COOKIE_NAME ) ) {
                $this->resume( Request::getInstance()->cookies()->get( self::SESSION_COOKIE_NAME ) );
            } else {
                $this->start();
            }
        }

        public function __destruct() {

            if ( $this->dataObj === null ) {
                return false;
            }

            $this->dataObj->setData( serialize( $this->currentData ) );
            $this->dataObj->save();
        }

        public function delete( $name ) {
            unset( $this->currentData[$name] );
            return $this;
        }

        public function destroy() {
            $this->dataObj->delete();
            $this->currentData = [];

            setcookie(
                self::SESSION_COOKIE_NAME, "", time() - self::SESSION_TIMEOUT * 10
            );
        }

        public function get( $name, $default = null ) {

            if ( $this->has( $name ) ) {
                return $this->currentData[$name];
            }

            return $default;
        }

        public function has( $name ) {
            return isset( $this->currentData[$name] );
        }

        /**
         * we have to copy it locally, since its not possible to to stuff like
         * $htis->storageObj::staticCall();
         *
         * @param type $sessionId
         * @return Session
         */
        private function resume( $sessionId ) {

            $localCopy = $this->storageObj;

            $this->dataObj = $localCopy::getBySessionId( $sessionId );

            // if not found, create new session
            if ( $this->dataObj === null ) {
                return $this->start();
            }

            $this->sessionId   = $sessionId;
            $this->currentData = unserialize( $this->dataObj->getData() );

            return $this;
        }

        /**
         *
         * @param string $name
         * @param mixed $value
         * @return Session
         */
        public function set( $name, $value ) {

            $this->currentData[$name] = $value;
            return $this;
        }

        private function start() {

            $this->sessionId = sha1( microtime( true ) . rand() );

            setcookie(
                self::SESSION_COOKIE_NAME, $this->sessionId, time() + self::SESSION_TIMEOUT, "/"
            );

            $this->dataObj = new $this->storageObj;

            $this->dataObj->setSessionId( $this->sessionId );
            $this->dataObj->setIp( Request::getInstance()->remoteIp() );
            $this->dataObj->setTimeout( time() + self::SESSION_TIMEOUT );
        }

        public function fetchSessionId() {
            return $this->sessionId;
        }
    }
