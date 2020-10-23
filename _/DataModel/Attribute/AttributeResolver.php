<?php

    namespace Wrapped\_\DataModel\Attribute;


    #[\Attribute]
    class AttributeResolver {

        public string $source;

        public string $dest;

        public function __construct( string $source, string $dest ) {
            $this->source = $source;
            $this->dest   = $dest;
        }

    }
