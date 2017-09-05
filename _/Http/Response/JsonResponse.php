<?php

    namespace Wrapped\_\Http\Response;

    class JsonResponse
    extends \Wrapped\_\Http\Response\Response {

        public function __construct( $content = null ) {

            $this->addHeader(
                new HttpHeader( "Content-type", "application/json" )
            );

            if ( $content !== null ) {
                $this->setContent( $content );
            }

            if ( $content instanceof \Wrapped\_\DataModel\Collection\CollectionResult ) {
                $this->setContent( $content->toArray() );
            }
        }

        public function setContent( $content ): Response  {
            return parent::setContent( json_encode( $content ) );
        }

    }
