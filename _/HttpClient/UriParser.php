<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient;

use verfriemelt\pp\Parser\Parser;
use verfriemelt\pp\Parser\ParserState;

use function verfriemelt\pp\Parser\functions\char;
use function verfriemelt\pp\Parser\functions\letters;
use function verfriemelt\pp\Parser\functions\manyOne;
use function verfriemelt\pp\Parser\functions\numbers;
use function verfriemelt\pp\Parser\functions\optional;
use function verfriemelt\pp\Parser\functions\regexp;
use function verfriemelt\pp\Parser\functions\sequenceOf;
use function verfriemelt\pp\Parser\functions\succeed;

final class UriParser
{
    public static function parser(): Parser
    {
        return sequenceOf(
            static::scheme(),
            static::userinfo(),
            static::host(),
            static::port(),
            static::path(),
            static::query(),
            static::fragment(),
        )->map(fn (array $result): array => [
            'scheme' =>  $result[0],
            'userInfo' =>  $result[1],
            'host' =>  $result[2],
            'port' =>  $result[3],
            'path' =>  $result[4],
            'query' =>  $result[5],
            'fragment' =>  $result[6],
        ]);
    }

    public static function scheme(): Parser
    {
        return optional(
            sequenceOf(
                letters(),
                char(':'),
                char('/'),
                char('/'),
            ),
        )->map(fn (?array $result) => $result[0] ?? '');
    }

    public static function userinfo(): Parser
    {
        return optional(
            sequenceOf(
                regexp('[^:@]+'),
                optional(
                    sequenceOf(
                        char(':'),
                        regexp('[^:@]+'),
                    )->map(fn (array $result) => \implode('', $result)),
                ),
                char('@')->map(fn () => ''),
            ),
        )->map(fn (?array $result, ParserState $state) => \is_array($result) ? \implode('', $result) : '');
    }

    public static function host(): Parser
    {
        return optional(regexp('^[a-zA-Z0-9\-\.]+'))->map(fn (?string $result) => \strtolower($result ?? ''));
    }

    public static function port(): Parser
    {
        return optional(
            char(':'),
        )->chain(function ($result) {

            if ($result !== ':') {
                return succeed(null);
            }

            return numbers();
        })->map(function (?string $result): ?int {

            if ($result === null) {
                return null;
            }

            return (int) $result;
        });
    }

    public static function path(): Parser
    {
        return optional(
            sequenceOf(
                manyOne(
                    char('/'),
                )->map(fn (mixed $result) => '/'),
                regexp('[^\?#]*'),
            )->map(fn (?array $result) => \implode('', $result)),
        )->map(fn (?string $result) => ($result ?? ''));
    }

    public static function query(): Parser
    {
        return optional(
            sequenceOf(
                char('?'),
                regexp('[^#]+'),
            ),
        )->map(fn (?array $result) => ($result[1] ?? ''));
    }

    public static function fragment(): Parser
    {
        return optional(
            sequenceOf(
                char('#'),
                regexp('.*'),
            ),
        )->map(fn (?array $result) => ($result[1] ?? ''));
    }
}
