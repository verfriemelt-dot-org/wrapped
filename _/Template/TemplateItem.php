<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Template;

    interface TemplateItem {

        public function run( &$source );
    }
