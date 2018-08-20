<?php

    namespace Wrapped\_\Http\Response;

    use \Wrapped\_\DataModel\Collection\CollectionResult;
    use \Wrapped\_\DataModel\DataModel;

    class JsonResponse
    extends Response {

        private $pretty = false;
        private $content = null;

        public function __construct( $content = null ) {

            $this->addHeader(
                new HttpHeader( "Content-type", "application/json" )
            );

            if ( $content instanceof CollectionResult ) {
                $this->setContent( $content->toArray() );
            } else {
                $this->setContent( $content );
            }
        }

        public function pretty( $bool = true): JsonResponse {
            $this->pretty = $bool;
            return $this;
        }

        public function setContent( $content ): Response {
            $this->content = $content;
            return $this;
        }

        public function send(): Response {

            if ( $this->content instanceof DataModel ) {
                parent::setContent( $this->content->toJson( $this->pretty ) );
            } else {
                parent::setContent( json_encode( $this->content , $this->pretty ? 128 : null ) );
            }

            return parent::send();
        }

    }
