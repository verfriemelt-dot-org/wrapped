<?php

    namespace Wrapped\_\Session;

    use \Wrapped\_\DataModel\DataModel;

    class SessionMysql
    extends DataModel
    implements \Wrapped\_\DataModel\TablenameOverride, SessionDataObject {

        public $id, $data, $timeout, $ip, $sessionId;

        public static function getBySessionId( $id ) {
            return static::findSingle( [ "sessionId" => $id ] );
        }

        public static function fetchTablename() {
            return "Session";
        }

        public function getId() {
            return $this->id;
        }

        public function getData() {
            return $this->data;
        }

        public function getTimeout() {
            return $this->timeout;
        }

        public function getIp() {
            return $this->ip;
        }

        public function getSessionId() {
            return $this->sessionId;
        }

        public function setId( $id ) {
            $this->id = $id;
            return $this;
        }

        public function setData( $data ) {
            $this->data = $data;
            return $this;
        }

        public function setTimeout( $timeout ) {
            $this->timeout = $timeout;
            return $this;
        }

        public function setIp( $ip ) {
            $this->ip = $ip;
            return $this;
        }

        public function setSessionId( $sessionId ) {
            $this->sessionId = $sessionId;
            return $this;
        }

    }
