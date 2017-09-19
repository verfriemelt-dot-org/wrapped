<?php namespace Wrapped\_\Session;

    Interface SessionDataObject {

        public static function getBySessionId( $id );
        public static function purgeOldSessions();

        public function getData();
        public function getTimeout();
        public function getIp();
        public function getSessionId();

        public function setData( $data );
        public function setTimeout( $timeout );
        public function setIp( $ip );
        public function setSessionId( $sessionId );

    }
