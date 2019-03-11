<?php

    namespace Wrapped\_\Queue\Backend;

    use \Wrapped\_\DataModel\DataModel;
    use \Wrapped\_\Queue\QueueItem;

    class MysqlBackendDataObject
    extends DataModel
    implements \Wrapped\_\DataModel\TablenameOverride {

        public $id, $uniqId, $channel, $key, $data, $startDate, $priority = 100, $locked   = 0;

        public static function fetchTablename(): string {
            return "QueueBackend";
        }

        public function write( QueueItem $item ) {

            $this->data = base64_encode( serialize( $item ) );

            $this->key       = $item->key;
            $this->uniqId    = $item->uniqId;
            $this->channel   = $item->channel;
            $this->priority  = $item->priority;
            $this->startDate = $item->startDate;

            return $this;
        }

        public function lock(): MysqlBackendDataObject {
            $this->locked = 1;
            return $this->save();
        }

        public function unlock(): MysqlBackendDataObject {
            $this->locked = 0;
            return $this->save();
        }

        public function read(): QueueItem {
            return unserialize( base64_decode( $this->data ) );
        }

        public function getId() {
            return $this->id;
        }

        public function getUniqId() {
            return $this->uniqId;
        }

        public function getChannel() {
            return $this->channel;
        }

        public function getData() {
            return $this->data;
        }

        public function setId( $id ) {
            $this->id = $id;
            return $this;
        }

        public function setUniqId( $uniqId ) {
            $this->uniqId = $uniqId;
            return $this;
        }

        public function setChannel( $channel ) {
            $this->channel = $channel;
            return $this;
        }

        public function setData( $data ) {
            $this->data = $data;
            return $this;
        }

        public function getKey() {
            return $this->key;
        }

        public function setKey( $key ) {
            $this->key = $key;
            return $this;
        }

        public function getPriority() {
            return $this->priority;
        }

        public function setPriority( $priority ) {
            $this->priority = $priority;
            return $this;
        }

        public function getStartDate() {
            return $this->startDate;
        }

        public function getLocked() {
            return $this->locked;
        }

        public function setStartDate( $startDate ) {
            $this->startDate = $startDate;
            return $this;
        }

        public function setLocked( $locked ) {
            $this->locked = $locked;
            return $this;
        }

    }
