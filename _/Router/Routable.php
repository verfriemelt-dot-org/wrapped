<?php

    declare(strict_types = 1);

    namespace Wrapped\_\Router;

    interface Routable {

        /**
         * should return set path for the route;
         * could be an reguluar expression eq /admin/site/(?<id>[0-9])
         */
        public function getPath();

        /**
         * defaults to 10; priorioty while 1 beeing more importatnt
         */
        public function getPriority();

        /**
         * returns the filter function for the route.
         * if that function returns true, the requests get filtered and
         * the callback wont be executed
         */
        public function getFilterCallback();
    }
