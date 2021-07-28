<?php

    use \verfriemelt\wrapped\_\Http\Request\Request;
    use \verfriemelt\wrapped\_\Input\Filter;

    class FilterTest
    extends \PHPUnit\Framework\TestCase {

        protected Request $request;

        public function setUp(): void {

            $_SERVER = array(
                'REDIRECT_HTTPS'        => 'on',
                'REDIRECT_SSL_TLS_SNI'  => 'next.21advertise.de',
                'REDIRECT_STATUS'       => '200',
                'HTTPS'                 => 'on',
                'SSL_TLS_SNI'           => 'next.21advertise.de',
                'HTTP_HOST'             => 'next.21advertise.de',
                'HTTP_CONNECTION'       => 'keep-alive',
                'HTTP_CACHE_CONTROL'    => 'max-age=0',
                'HTTP_ACCEPT'           => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'HTTP_USER_AGENT'       => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
                'HTTP_ACCEPT_ENCODING'  => 'gzip,deflate,sdch',
                'HTTP_ACCEPT_LANGUAGE'  => 'en-US,en;q=0.8,de;q=0.6',
                'HTTP_COOKIE'           => 'trac_form_token=95a3658d2131176c5e560a65; PHPSESSID=bsa15rkm2h49eu0ehuroo9pbc1; id=106b61aa3bf852a79064988cc22464933a2fcbe6',
                'PATH'                  => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
                'SERVER_SIGNATURE'      => '<address>Apache/2.4.9 (Debian) Server at next.21advertise.de Port 443</address>',
                'SERVER_SOFTWARE'       => 'Apache/2.4.9 (Debian)',
                'SERVER_NAME'           => 'next.21advertise.de',
                'SERVER_ADDR'           => '78.46.85.5',
                'SERVER_PORT'           => '443',
                'REMOTE_ADDR'           => '80.153.193.92',
                'DOCUMENT_ROOT'         => '/var/www/next.21advertise.de',
                'REQUEST_SCHEME'        => 'https',
                'CONTEXT_PREFIX'        => '',
                'CONTEXT_DOCUMENT_ROOT' => '/var/www/next.21advertise.de',
                'SERVER_ADMIN'          => '[no address given]',
                'SCRIPT_FILENAME'       => '/var/www/next.21advertise.de/index.php',
                'REMOTE_PORT'           => '48499',
                'REDIRECT_URL'          => '/dumpFiles',
                'GATEWAY_INTERFACE'     => 'CGI/1.1',
                'SERVER_PROTOCOL'       => 'HTTP/1.1',
                'REQUEST_METHOD'        => 'GET',
                'QUERY_STRING'          => '',
                'REQUEST_URI'           => '/dumpFiles',
                'SCRIPT_NAME'           => '/index.php',
                'PATH_INFO'             => '/dumpFiles',
                'PATH_TRANSLATED'       => 'redirect:/index.php/dumpFiles',
                'PHP_SELF'              => '/index.php/dumpFiles',
                'REQUEST_TIME_FLOAT'    => 1406104894.0079999,
                'REQUEST_TIME'          => 1406104894,
                'HTTP_REFERER'          => "google.de"
            );

            $_COOKIE = [ "testCookie" => "testValue" ];
            $_GET    = array(
                'foo' => array(
                    0      => 'test1',
                    1      => 'test2',
                    'test' => 'test3'
                ),
                'bar' => 'win',
                "utf" => "èµ€"
            );

            $_POST = [
                "test"  => "a",
                "test2" => [ 3, 7 ]
            ];

            $this->request = new Request( $_GET, $_POST, [], $_COOKIE, [], $_SERVER, "" );
        }

        public function testFilterCreation() {
            $this->assertTrue( new Filter( $this->request ) instanceof Filter );
        }

        public function testFilterLength() {

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->minLength( 5 );
            $this->assertFalse( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->maxLength( 2 );
            $this->assertFalse( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->minLength( 2 );
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->maxLength( 6 );
            $this->assertTrue( $filter->validate() );
        }

        public function testOptionalFilters() {

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->maxLength( 6 )->optional();
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "bar" )->maxLength( 2 )->optional();
            $this->assertFalse( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "notExisting" )->maxLength( 2 )->optional();
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "notExisting" )->maxLength( 2 );
            $this->assertFalse( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->this( "notExisting" )->required();
            $this->assertFalse( $filter->validate() );
            }

            public function testCookiesFilter() {

            $filter = new Filter( $this->request );
            $filter->cookies()->this( "bar" )->optional();
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->cookies()->this( "testCookie" )->optional();
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->cookies()->this( "testCookie" )->required();
            $this->assertTrue( $filter->validate() );
        }

        public function testMultipleFiltersAtOnce() {
            $filter = new Filter( $this->request );
            $filter->query()->has( "bar" )->minLength( 3 )->maxLength( 3 )->allowedChars( "inwaè" );
            $this->assertTrue( $filter->validate() );
        }

        public function testUTFStuff() {
            $filter = new Filter( $this->request );
            $filter->query()->has( "utf" )->minLength( 3 )->maxLength( 3 )->allowedChars( "èµ€" );
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->query()->has( "utf" )->minLength( 3 )->maxLength( 3 )->allowedChars( "è" );
            $this->assertFalse( $filter->validate() );
        }

        public function testSetPredefinedValues() {

            $filter = new Filter( $this->request );
            $filter->request()->has( "test" )->allowedValues( [ "a", "b", "c" ] );
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->request()->has( "test" )->allowedValues( [ "null", "c" ] );
            $this->assertFalse( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->request()->has( "test2" )->multiple()->allowedValues( [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ] );
            $this->assertTrue( $filter->validate() );

            $filter = new Filter( $this->request );
            $filter->request()->has( "test2" )->multiple()->allowedValues( [ 1, 2, 4, 5, 6, 7, 8, 9 ] );
            $this->assertFalse( $filter->validate() );
        }

    }
