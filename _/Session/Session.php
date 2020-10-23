<?php

    namespace Wrapped\_\Session;

    use \Wrapped\_\Http\Request\Request;
    use \Wrapped\_\Singleton;

    class Session
    implements SessionHandler {

        use Singleton;

        CONST SESSION_COOKIE_NAME = "_";
        CONST SESSION_TIMEOUT     = 60 * 60 * 24 * 365;

        private $dataObj     = null;
        private $sessionId   = null;
        private $storageObj  = null;
        private $currentData = [];

        private function __construct( SessionDataObject $sessionStorage = null, Request $request = null ) {

            if ( $sessionStorage === null || class_implements( $sessionStorage, SessionDataObject::class ) ) {
                $this->storageObj = SessionSql::class;
            } else {
                $this->storageObj = $sessionStorage;
            }

            $this->storageObj::purgeOldSessions();

            $request = $request ?? Request::getInstance();

            if ( $request->cookies()->has( self::SESSION_COOKIE_NAME ) ) {
                $this->resume( $request->cookies()->get( self::SESSION_COOKIE_NAME ) );
            }
        }

        public function __destruct() {

            if ( $this->dataObj === null ) {
                return false;
            }

            $this->dataObj->setTimeout( time() + static::SESSION_TIMEOUT );
            $this->dataObj->setData( base64_encode(serialize( $this->currentData )) );
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
            $this->currentData = unserialize( base64_decode( $this->dataObj->getData() ) );

            return $this;
        }

        /**
         *
         * @param string $name
         * @param mixed $value
         * @return Session
         */
        public function set( $name, $value ) {

            if ( $this->sessionId === null ) {
                $this->start();
            }

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

            if ( $this->sessionId === null ) {
                $this->start();
            }

            return $this->sessionId;
        }

    }
