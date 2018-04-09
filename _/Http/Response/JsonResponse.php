<?php

    namespace Wrapped\_\Http\Response;

    use \Wrapped\_\DataModel\Collection\CollectionResult;
    use \Wrapped\_\DataModel\DataModel;

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

            if ( $content instanceof DataModel ) {
                $content = $content->toJson();
            } else {
                $content = json_encode( $content );
            }

            return parent::setContent( $content );
        }

    }
