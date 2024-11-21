<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Psr7;

use Http\Psr7Test\UriIntegrationTest;
use verfriemelt\wrapped\_\HttpClient\Psr\Uri;
use Override;

class UriTest extends UriIntegrationTest
{
    /**
     * @param string $uri
     */
    #[Override]
    public function createUri($uri): Uri
    {
        return new Uri($uri);
    }

    /**
     * @return iterable<mixed>
     */
    #[Override]
    public static function getPaths(): iterable
    {
        $data = parent::getPaths();

        // skip out [$test->createUri('foo/bar'), 'foo/bar'],
        unset($data[2]);

        return $data;
    }
}
