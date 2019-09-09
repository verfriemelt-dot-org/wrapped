<?php namespace Wrapped\_\DateTime;

    class DateTime extends \DateTime {

        const SQL_FORMAT = "Y-m-d H:i:s";

        public function toSqlFormat(): string {
            return $this->format( self::SQL_FORMAT );
        }

    }
