<?php

    declare(strict_types = 1);

    namespace verfriemelt\wrapped\_\Template;

    interface TemplateItem {

        public function run( &$source );
    }
