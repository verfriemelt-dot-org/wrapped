<?php

    namespace Wrapped\_\Queue\Backend;

    use \Wrapped\_\Database\DbLogic;
    use \Wrapped\_\Queue\Interfaces\QueuePersistance;
    use \Wrapped\_\Queue\Queue;
    use \Wrapped\_\Queue\QueueItem;

    class MysqlBackend
    implements QueuePersistance {

        public function deleteItem( QueueItem $item ): bool {

            $instance = MysqlBackendDataObject::findSingle( [ "uniqId" => $item->uniqId ] );

            if ( !$instance ) {
                return false;
            }

            $instance->delete();
            return true;
        }

        public function fetchByUniqueId( $uniqueId ) {
            $item = MysqlBackendDataObject::findSingle( [ "uniqId" => $uniqueId ] );
            return $item ? $item->read() : null;
        }

        public function fetchByKey( string $key, string $channel = Queue::DEFAULT_CHANNEL, int $limit = null ): array {

            $logic = DbLogic::create()
                ->where( "channel", "=", $channel )->addAnd()
                ->where( "key", "=", $key )->addAnd()
                ->where( "locked", "=", 0 )
                ->order( "date", "asc" );

            if ( $limit ) {
                $logic->limit( $limit );
            }

            $collection = MysqlBackendDataObject::find( $logic );
            $queueItems = $collection->propagateCall( "read" );

            return $queueItems;
        }

        public function fetchChannel( string $channel = Queue::DEFAULT_CHANNEL, int $limit = null ): array {

            $logic = DbLogic::create()
                ->where( "channel", "=", $channel )->addAnd()
                ->where( "locked", "=", 0 )
                ->order( "date", "asc" );

            if ( $limit ) {
                $logic->limit( $limit );
            }

            $collection = MysqlBackendDataObject::find( $logic );
            $queueItems = $collection->propagateCall( "read" );

            return $queueItems;
        }

        public function purge(): bool {
            // nope
        }

        public function store( QueueItem $item ) {

            $backend = new MysqlBackendDataObject();
            $backend->write( $item );
            $backend->save();

            return $this;
        }

        public function lock( QueueItem $item ): bool {

            $item = MysqlBackendDataObject::findSingle( [ "uniqId" => $item->uniqId ] );

            if ( !$item ) {
                return false;
            }

            $item->lock();
            return true;
        }

        public function unlock( QueueItem $item ): bool {

            $item = MysqlBackendDataObject::findSingle( [ "uniqId" => $item->uniqId ] );

            if ( !$item ) {
                return false;
            }

            $item->unlock();
            return true;
        }

    }
