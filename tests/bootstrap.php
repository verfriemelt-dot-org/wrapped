<?php

    use \Wrapped\Bootstrap;

//die(__DIR__ . '/../_/');
//    CODECEPTION\UTIL\AUTOLOAD::addNamespace( 'wrapped', __DIR__ . '/../_/' );


    require __DIR__ . '/../Bootstrap.php';

    Bootstrap::registerAutoloader();
    Bootstrap::registerExceptionHandling();
