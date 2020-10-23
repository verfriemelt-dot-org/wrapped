<?php

    namespace Wrapped\_\DataModel\Attribute\Naming;

    abstract class Convention {

        public const DESTRUCTIVE = false;

        public function __construct( ?string $str = null ) {
            if ( $str ) {
                $this->setString( $str );
            }
        }

        public function setString( string $str ) {
            $this->str = $str;
            return $this;
        }

        public function getString(): string {
            return $this->str;
        }

        public function convertTo( $class ) {

            if ( static::DESTRUCTIVE ) {
                throw new \Exception('not possible');
            }

            return $class::fromStringParts( ... $this->fetchStringParts() );
        }

        abstract public function fetchStringParts(): array;

        abstract static public function fromStringParts( string ... $parts ): Convention;
    }
