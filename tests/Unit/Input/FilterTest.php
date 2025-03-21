<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Input;

use Override;
use verfriemelt\wrapped\_\Http\Request\Request;
use verfriemelt\wrapped\_\Http\Request\RequestStack;
use verfriemelt\wrapped\_\Input\Filter;

// TODO: jesus is that test ugly, please rewrite
class FilterTest extends \PHPUnit\Framework\TestCase
{
    protected Request $request;
    private Filter $filter;
    private RequestStack $requestStack;

    #[Override]
    public function setUp(): void
    {
        $server = [
            'REDIRECT_HTTPS' => 'on',
            'REDIRECT_SSL_TLS_SNI' => 'next.21advertise.de',
            'REDIRECT_STATUS' => '200',
            'HTTPS' => 'on',
            'SSL_TLS_SNI' => 'next.21advertise.de',
            'HTTP_HOST' => 'next.21advertise.de',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.1916.153 Safari/537.36',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate,sdch',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.8,de;q=0.6',
            'HTTP_COOKIE' => 'trac_form_token=95a3658d2131176c5e560a65; PHPSESSID=bsa15rkm2h49eu0ehuroo9pbc1; id=106b61aa3bf852a79064988cc22464933a2fcbe6',
            'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
            'SERVER_SIGNATURE' => '<address>Apache/2.4.9 (Debian) Server at next.21advertise.de Port 443</address>',
            'SERVER_SOFTWARE' => 'Apache/2.4.9 (Debian)',
            'SERVER_NAME' => 'next.21advertise.de',
            'SERVER_ADDR' => '78.46.85.5',
            'SERVER_PORT' => '443',
            'REMOTE_ADDR' => '80.153.193.92',
            'DOCUMENT_ROOT' => '/var/www/next.21advertise.de',
            'REQUEST_SCHEME' => 'https',
            'CONTEXT_PREFIX' => '',
            'CONTEXT_DOCUMENT_ROOT' => '/var/www/next.21advertise.de',
            'SERVER_ADMIN' => '[no address given]',
            'SCRIPT_FILENAME' => '/var/www/next.21advertise.de/index.php',
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

        $cookie = ['testCookie' => 'testValue'];
        $get = [
            'foo' => [
                0 => 'test1',
                1 => 'test2',
                'test' => 'test3',
            ],
            'bar' => 'win',
            'utf' => 'èµ€',
        ];

        $post = [
            'test' => 'a',
            'test2' => [3, 7],
        ];

        $this->request = new Request($get, $post, [], $cookie, [], $server, '');
        $this->requestStack = new RequestStack();
        $this->requestStack->push($this->request);
    }

    public function test_filter_length(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->minLength(5);
        static::assertFalse($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->maxLength(2);
        static::assertFalse($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->minLength(2);
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->maxLength(6);
        static::assertTrue($this->filter->validate());
    }

    public function test_optional_filters(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->maxLength(6)->optional();
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('bar')->maxLength(2)->optional();
        static::assertFalse($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('notExisting')->maxLength(2)->optional();
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('notExisting')->maxLength(2);
        static::assertFalse($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->this('notExisting')->required();
        static::assertFalse($this->filter->validate());
    }

    public function test_cookies_filter(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->cookies()->this('bar')->optional();
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->cookies()->this('testCookie')->optional();
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->cookies()->this('testCookie')->required();
        static::assertTrue($this->filter->validate());
    }

    public function test_multiple_filters_at_once(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->has('bar')->minLength(3)->maxLength(3)->allowedChars('inwaè');
        static::assertTrue($this->filter->validate());
    }

    public function test_utf_stuff(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->has('utf')->minLength(3)->maxLength(3)->allowedChars('èµ€');
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->query()->has('utf')->minLength(3)->maxLength(3)->allowedChars('è');
        static::assertFalse($this->filter->validate());
    }

    public function test_set_predefined_values(): void
    {
        $this->filter = new Filter($this->requestStack);
        $this->filter->request()->has('test')->allowedValues(['a', 'b', 'c']);
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->request()->has('test')->allowedValues(['null', 'c']);
        static::assertFalse($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->request()->has('test2')->multiple()->allowedValues([1, 2, 3, 4, 5, 6, 7, 8, 9]);
        static::assertTrue($this->filter->validate());

        $this->filter = new Filter($this->requestStack);
        $this->filter->request()->has('test2')->multiple()->allowedValues([1, 2, 4, 5, 6, 7, 8, 9]);
        static::assertFalse($this->filter->validate());
    }
}
