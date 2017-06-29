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
        }

        public function setContent( $content ) {
            return parent::setContent( json_encode( $content ) );
        }

    }
