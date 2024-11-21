<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\HttpClient;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use verfriemelt\pp\Parser\ParserInput;
use verfriemelt\wrapped\_\HttpClient\UriParser;

class UriParserTest extends TestCase
{
    /**
     * @return iterable<mixed>
     */
    public static function scheme(): iterable
    {
        yield 'none' => ['google.de', ''];
        yield 'ftp' => ['ftp://google.de', 'ftp'];
        yield 'https' => ['https://google.de', 'https'];
    }

    #[DataProvider('scheme')]
    public function test_scheme(string $input, string $expected): void
    {
        $parser = UriParser::scheme();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function userinfo(): iterable
    {
        yield 'none' => ['', ''];
        yield 'username' => ['user@', 'user'];
        yield 'password' => ['user:password@', 'user:password'];
        yield 'encoded' => ['foo%40bar.com:pass%23word@', 'foo%40bar.com:pass%23word'];
    }

    #[DataProvider('userinfo')]
    public function test_userinfo(string $input, string $expected): void
    {
        $parser = UriParser::userinfo();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function port(): iterable
    {
        yield 'none' => ['', null];
        yield '80' => [':80', 80];
    }

    #[DataProvider('port')]
    public function test_port(string $input, ?int $expected): void
    {
        $parser = UriParser::port();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function host(): iterable
    {
        yield 'none' => ['', ''];
        yield 'foo.bar' => ['foo.bar', 'foo.bar'];
    }

    #[DataProvider('host')]
    public function test_host(string $input, ?string $expected): void
    {
        $parser = UriParser::host();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function path(): iterable
    {
        yield 'none' => ['', ''];
        yield '/foo/bar.html' => ['/foo/bar.html', '/foo/bar.html'];
        yield '//valid///path' => ['//valid///path', '/valid///path'];
    }

    #[DataProvider('path')]
    public function test_path(string $input, ?string $expected): void
    {
        $parser = UriParser::path();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function query(): iterable
    {
        yield 'none' => ['', ''];
        yield '?foo=bar' => ['?foo=bar', 'foo=bar'];
        yield '?foo=bar&bar=foo' => ['?foo=bar&bar=foo', 'foo=bar&bar=foo'];
    }

    #[DataProvider('query')]
    public function test_query(string $input, ?string $expected): void
    {
        $parser = UriParser::query();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    /**
     * @return iterable<mixed>
     */
    public static function fragment(): iterable
    {
        yield 'none' => ['', ''];
        yield '#foo=bar' => ['#foo=bar', 'foo=bar'];
        yield '#foo=bar&bar=foo' => ['#foo=bar&bar=foo', 'foo=bar&bar=foo'];
    }

    #[DataProvider('fragment')]
    public function test_fragment(string $input, ?string $expected): void
    {
        $parser = UriParser::fragment();
        $result = $parser->run(new ParserInput($input));

        static::assertSame($expected, $result->getResult());
    }

    public function test_missing_number_after_port(): void
    {
        $parser = UriParser::port();
        $result = $parser->run(new ParserInput(':'));

        static::assertTrue($result->isError());
    }

    public function test_regression_with_only_path(): void
    {
        $result = UriParser::parser()->run(new ParserInput('/foobar'))->getResult();

        static::assertIsArray($result);
        static::assertCount(7, $result);
        static::assertSame('', $result['host'], 'host part empty');
        static::assertSame('/foobar', $result['path'], 'uri part populated');

    }
}
