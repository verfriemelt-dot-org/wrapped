<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\StreamInterface;
use Override;

final class StreamFactory implements \Psr\Http\Message\StreamFactoryInterface
{
    #[Override]
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = \fopen('php://memory', 'r+');
        \assert(\is_resource($stream));
        fwrite($stream, $content);
        \rewind($stream);

        return new Stream($stream);
    }

    #[Override]
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $stream = \fopen($filename, $mode);
        \assert(\is_resource($stream));

        return new Stream($stream);
    }

    #[Override]
    public function createStreamFromResource($resource): StreamInterface
    {
        \assert(\is_resource($resource));

        return new Stream($resource);
    }
}
