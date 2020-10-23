<?php

    namespace Wrapped\_\DataModel\Attribute;

    #[\Attribute]
    class PropertyResolver {

        public string $sourceProperty;

        public string $destinationProperty;

        public function __construct( string $source, string $dest ) {
            $this->sourceProperty      = $source;
            $this->destinationProperty = $dest;
        }

    }
