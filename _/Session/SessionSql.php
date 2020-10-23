<?php

    namespace Wrapped\_\Session;

    use \Wrapped\_\Database\SQL\Clause\Where;
    use \Wrapped\_\Database\SQL\Command\Delete;
    use \Wrapped\_\Database\SQL\Expression\Expression;
    use \Wrapped\_\Database\SQL\Expression\Identifier;
    use \Wrapped\_\Database\SQL\Expression\Operator;
    use \Wrapped\_\Database\SQL\Expression\Value;
    use \Wrapped\_\Database\SQL\Statement;
    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\DataModel\TablenameOverride;

    class SessionSql
    extends DataModel
    implements TablenameOverride, SessionDataObject {

        public $id, $data, $timeout, $ip, $sessionId;

        public static function getBySessionId( $id ) {
            return static::findSingle( [ "sessionId" => $id ] );
        }

        public static function purgeOldSessions() {

            $stmt = new Statement( new Delete( new Identifier( static::getTableName() ) ) );
            $stmt->add( new Where( (new Expression () )
                        ->add( new Identifier( 'timeout' ) )
                        ->add( new Operator( '<' ) )
                        ->add( new Value( time() ) )
            ) );

            static::getDatabase()->run( $stmt );
        }

        public static function fetchTablename(): string {
            return "session";
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