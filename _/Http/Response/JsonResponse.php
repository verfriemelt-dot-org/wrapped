<?php

    namespace Wrapped\_\Http\Response;

    use \Wrapped\_\DataModel\Collection\CollectionResult;

    class JsonResponse
    extends Response {

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

        public function setContent( $content ): Response {
            return parent::setContent( json_encode( $content ) );
        }

    }
