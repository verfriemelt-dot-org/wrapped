<?php

declare(strict_types=1);

use verfriemelt\wrapped\_\Http\Request\Request;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        $_SERVER = [
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
            'REQUEST_TIME_FLOAT' => 1_406_104_894.0079999,
            'REQUEST_TIME' => 1_406_104_894,
            'HTTP_REFERER' => 'google.de',
        ];

        $_COOKIE = ['testCookie' => 'testValue'];
        $_GET = ['foo' => [0 => 'test1', 1 => 'test2', 'test' => 'test3'], 'bar' => '1'];
    }

    public function test_can_detect_http(): void
    {
        $request = Request::createFromGlobals();
        static::assertTrue($request->secure());
    }

    public function test_can_retrieve_cookie_informations(): void
    {
        $request = Request::createFromGlobals();

        static::assertTrue($request->cookies()->has('testCookie'));
        static::assertFalse($request->cookies()->has('!testCookie'));

        static::assertSame('testValue', $request->cookies()->get('testCookie'));

        static::assertTrue($request->cookies()->is('testCookie', 'testValue'));
        static::assertFalse($request->cookies()->is('testCookie', '!testValue'));
    }

    public function test_can_i_have_get_params(): void
    {
        $request = Request::createFromGlobals();
        static::assertSame($_GET, $request->query()->all());

        static::assertSame([0 => 'test1', 1 => 'test2', 'test' => 'test3'], $request->query()->first());
        static::assertSame('1', $request->query()->last(), 'fetching last item');
        static::assertSame(2, $request->query()->count(), 'counting items');
        static::assertTrue($request->query()->isNot('bar', 2), 'is NOT');

        static::assertSame(['bar' => '1'], $request->query()->except(['foo']), 'fetching items except one');

        foreach ($request->query() as $key => $item) {
            static::assertTrue(isset($_GET[$key]));
            static::assertSame($_GET[$key], $item);
        }

        static::assertSame('default value', $request->query()->get('nope', 'default value'), 'getting defaults');
    }

    public function test_can_i_get_referer(): void
    {
        static::assertSame($_SERVER['HTTP_REFERER'], Request::createFromGlobals()->referer());
    }

    public function test_aggregate(): void
    {
        $request = new Request(['win' => 1], ['bar' => 2], [], ['foo' => 3]);

        static::assertSame(1, $request->aggregate()->get('win'));
        static::assertSame(2, $request->aggregate()->get('bar'));
        static::assertSame(3, $request->aggregate()->get('foo'));
    }

    public function test_aggregate_overwrite(): void
    {
        $request = new Request(['win' => 1], ['win' => 2], [], ['win' => 3]);

        static::assertSame(1, $request->aggregate()->get('win'));
    }
}
