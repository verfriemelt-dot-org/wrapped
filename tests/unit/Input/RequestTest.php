<?php

    use \Wrapped\_\Http\Request\Request;

    class RequestTest
    extends PHPUnit_Framework_TestCase {

        public function setUp() {

            $_SERVER = array(
                'REDIRECT_HTTPS' => 'on',
                'REDIRECT_SSL_TLS_SNI' => 'localhost.de',
                'REDIRECT_STATUS' => '200',
                'HTTPS' => 'on',
                'SSL_TLS_SNI' => 'localhost.de',
                'HTTP_HOST' => 'localhost.de',
                'HTTP_CONNECTION' => 'keep-alive',
                'HTTP_CACHE_CONTROL' => 'max-age=0',
                'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
                'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
                'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,de;q=0.6',
                'HTTP_COOKIE' => 'trac_form_token=95a3658d2131176c5e560a65; PHPSESSID=bsa15rkm2h49eu0ehuroo9pbc1; id=106b61aa3bf852a79064988cc22464933a2fcbe6',
                'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                'SERVER_SIGNATURE' => '<address>Apache/2.4.9 (Debian) Server at localhost.de Port 443</address>',
                'SERVER_SOFTWARE' => 'Apache/2.4.9 (Debian)',
                'SERVER_NAME' => 'localhost.de',
                'SERVER_ADDR' => '127.0.0.1',
                'SERVER_PORT' => '443',
                'REMOTE_ADDR' => '127.0.0.1',
                'DOCUMENT_ROOT' => '/var/www/localhost.de',
                'REQUEST_SCHEME' => 'https',
                'CONTEXT_PREFIX' => '',
                'CONTEXT_DOCUMENT_ROOT' => '/var/www/localhost.de',
                'SERVER_ADMIN' => '[no address given]',
                'SCRIPT_FILENAME' => '/var/www/localhost.de/index.php',
                'REMOTE_PORT' => '48499',
                'REDIRECT_URL' => '/dumpFiles',
                'GATEWAY_INTERFACE' => 'CGI/1.1',
                'SERVER_PROTOCOL' => 'HTTP/1.1',
                'REQUEST_METHOD' => 'GET',
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/dumpFiles',
                'SCRIPT_NAME' => '/index.php',
                'PATH_INFO' => '/dumpFiles',
                'PATH_TRANSLATED' => 'redirect:/index.php/dumpFiles',
                'PHP_SELF' => '/index.php/dumpFiles',
                'REQUEST_TIME_FLOAT' => 1406104894.0079999,
                'REQUEST_TIME' => 1406104894,
                'HTTP_REFERER' => "google.de"
            );

            $_COOKIE = [ "testCookie" => "testValue" ];
            $_GET = array( 'foo' => array( 0 => 'test1', 1 => 'test2', 'test' => 'test3' ), 'bar' => '1', );
        }

        public function testCanCreateInstance() {
            Request::createFromGlobals();
        }

        public function testCanDetectHttp() {
            $request = Request::createFromGlobals();
            $this->assertTrue( $request->secure() );
        }

        public function testCanRetrieveCookieInformations() {
            $request = Request::createFromGlobals();

            $this->assertTrue( $request->cookies()->has( "testCookie" ) );
            $this->assertFalse( $request->cookies()->has( "!testCookie" ) );

            $this->assertEquals( "testValue", $request->cookies()->get( "testCookie" ) );

            $this->assertTrue( $request->cookies()->is( "testCookie", "testValue" ) );
            $this->assertFalse( $request->cookies()->is( "testCookie", "!testValue" ) );
        }

        public function testCanIHaveGetParams() {
            $request = Request::createFromGlobals();
            $this->assertEquals( $_GET, $request->query()->all() );

            $this->assertEquals( array( 0 => 'test1', 1 => 'test2', 'test' => 'test3' ), $request->query()->first() );
            $this->assertEquals( 1, $request->query()->last(), "fetching last item" );
            $this->assertEquals( 2, $request->query()->count(), "counting items" );
            $this->assertTrue( $request->query()->isNot( "bar", 2 ), "is NOT" );

            $this->assertEquals( ["bar" => 1 ], $request->query()->except( ["foo" ] ), "fetching items except one" );

            foreach ( $request->query() as $key => $item ) {
                $this->assertTrue( isset( $_GET[$key] ) );
                $this->assertTrue( $_GET[$key] == $item );
            }

            $this->assertEquals( "default value", $request->query()->get( "nope", "default value" ), "getting defaults" );
        }

        public function testCanIGetReferer() {
            $this->assertEquals( $_SERVER["HTTP_REFERER"], Request::createFromGlobals()->referer() );
        }

        public function testAggregate() {

            $request = new Request( [ "win" => 1 ], [ "bar" => 2 ], [ ], [ "foo" => 3 ]);

            $this->assertEquals( 1, $request->aggregate()->get( "win" ) );
            $this->assertEquals( 2, $request->aggregate()->get( "bar" ) );
            $this->assertEquals( 3, $request->aggregate()->get( "foo" ) );
        }

        public function testAggregateOverwrite() {
            $request = new Request( [ "win" => 1 ], [ "win" => 2 ], [ ], [ "win" => 3 ]);

            $this->assertEquals( 1, $request->aggregate()->get( "win" ) );
        }

    }
