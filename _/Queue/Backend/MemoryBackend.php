<?php

    namespace Wrapped\_\Queue\Backend;

    use \Wrapped\_\Queue\Interfaces\QueuePersistance;
    use \Wrapped\_\Queue\Queue;
    use \Wrapped\_\Queue\QueueItem;

    class MemoryBackend
    implements QueuePersistance {

        private $storage = [];

        public function store( QueueItem $item ) {

            $this->storage[$item->channel][] = [
                "item"   => $item,
                "locked" => false
            ];

            return $this;
        }

        public function fetchByKey( string $key, string $channel = Queue::DEFAULT_CHANNEL, int $count = null ): array {

            $result = [];

            foreach ( $this->fetchChannel( $channel ) as &$item ) {

                if ( $item->key !== $key ) {
                    continue;
                }

                $result[] = $item;
            }

            return $result;
        }

        public function fetchChannel( string $channel = Queue::DEFAULT_CHANNEL, int $count = null ): array {

            $result = [];

            if ( !isset( $this->storage[$channel] ) ) {
                return [];
            }

            foreach ( $this->storage[$channel] as $element ) {

                if ( $element["locked"] === true ) {
                    continue;
                }

                $result[] = $element["item"];
            }

            return $result;
        }

        public function deleteItem( QueueItem $item ): bool {

            if ( !isset( $this->storage[$item->channel] ) ) {
                return false;
            }

            foreach ( $this->storage[$item->channel] as $key => &$element ) {

                $itemOnQueue = $element["item"];

                if ( $item->uniqId === $itemOnQueue->uniqId ) {
                    unset( $this->storage[$item->channel][$key] );
                    return true;
                }
            }

            return false;
        }

        public function purge(): bool {
            $this->storage = [];
            return true;
        }

        public function lock( QueueItem $item ): bool {

            if ( !isset( $this->storage[$item->channel] ) ) {
                return false;
            }

            foreach ( $this->storage[$item->channel] as $key => &$element ) {

                $itemOnQueue = $element["item"];

                if ( $item->uniqId === $itemOnQueue->uniqId ) {
                    $element["locked"] = true;

                    return true;
                }
            }

            return false;
        }

        public function unlock( QueueItem $item ): bool {

            if ( !isset( $this->storage[$item->channel] ) ) {
                return false;
            }

            foreach ( $this->storage[$item->channel] as $key => &$element ) {

                $itemOnQueue = $element["item"];

                if ( $item->uniqId === $itemOnQueue->uniqId ) {
                    $element["locked"] = false;
                    return true;
                }
            }

            return false;
        }

    }
