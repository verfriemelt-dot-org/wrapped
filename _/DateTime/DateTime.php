<?php namespace Wrapped\_\DateTime;

    class DateTime extends \DateTime {

        const MYSQL_FORMAT = "Y-m-d H:i:s";

        public function toMysqlFormat(): string {
            return $this->format( self::MYSQL_FORMAT );
        }

    }
