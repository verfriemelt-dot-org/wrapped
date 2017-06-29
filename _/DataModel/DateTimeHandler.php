<?php

    namespace Wrapped\_\DataModel;

    use \DateTime;

    trait DateTimeHandler {

        /**
         * converts instance of datetime to unix timestamps
         * otherwise just pass the string through
         * @return string
         */
        public function dateTimeToUnix( $input ) {

            if ( $input instanceof DateTime ) {
                return $input->format( "U" );
            }

            return $input;
        }

        /**
         * converts instance of datetime to mysql timestamps
         * otherwise just pass the string through
         * @return string
         */
        public function dateTimeToMysql( $input ) {
            if ( $input instanceof DateTime ) {
                return $input->format( "Y-m-d H:i:s" );
            }

            return $input;
        }

    }
