<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\StreamInterface;
use Exception;
use Override;
use RuntimeException;

final class Stream implements StreamInterface
{
    /**
     * @var array{
     *    timed_out: bool,
     *    blocked: bool,
     *    eof: bool,
     *    unread_bytes: int,
     *    stream_type: string,
     *    wrapper_type: string,
     *    wrapper_data: mixed,
     *    mode: string,
     *    seekable: bool,
     *    uri: string,
     * }
     */
    private array $meta;

    /**
     * @param resource $stream
     */
    public function __construct(
        private $stream,
    ) {
        \assert(\is_resource($this->stream));

        $this->meta = \stream_get_meta_data($this->stream);
    }

    #[Override]
    public function __toString(): string
    {
        $string = \stream_get_contents($this->stream, null, 0);
        if ($string === false) {
            throw new RuntimeException('Unable to read stream');
        }

        return $string;
    }

    #[Override]
    public function close(): void
    {
        fclose($this->stream);
    }

    /**
     * @return resource
     */
    #[Override]
    public function detach()
    {
        $result = $this->stream;
        unset($this->stream);
        return $result;
    }

    #[Override]
    public function getSize(): ?int
    {
        $stats = fstat($this->stream);
        if ($stats === false) {
            return null;
        }

        assert(\array_key_exists('size', $stats));

        return $stats['size'];
    }

    #[Override]
    public function tell(): int
    {
        $pos = \ftell($this->stream);
        if ($pos === false) {
            throw new RuntimeException('unable to determine stream position');
        }

        return $pos;
    }

    #[Override]
    public function eof(): bool
    {
        return \feof($this->stream);
    }

    #[Override]
    public function isSeekable(): bool
    {
        return $this->meta['seekable'];
    }

    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->meta['seekable']) {
            throw new RuntimeException('stream not seekable');
        }

        \fseek($this->stream, $offset, $whence);
    }

    #[Override]
    public function rewind(): void
    {
        if (!$this->meta['seekable']) {
            throw new RuntimeException('stream not seekable');
        }

        \rewind($this->stream);
    }

    #[Override]
    public function isWritable(): bool
    {
        return \str_contains($this->meta['mode'], 'w');
    }

    #[Override]
    public function write(string $string): int
    {
        $bytes = \fwrite($this->stream, $string);

        if ($bytes === false) {
            throw new RuntimeException('write failed');
        }

        return $bytes;
    }

    #[Override]
    public function isReadable(): bool
    {
        return true;
    }

    #[Override]
    public function read(int $length): string
    {
        \assert($length > 0);

        try {
            $string = \fread($this->stream, $length);
        } catch (Exception $e) {
            throw new RuntimeException('unable to read from stream', 0, $e);
        }

        if (false === $string) {
            throw new RuntimeException('unable to read from stream');
        }

        return $string;
    }

    #[Override]
    public function getContents(): string
    {
        $string = \stream_get_contents($this->stream);
        if ($string === false) {
            throw new RuntimeException('Unable to read stream');
        }

        return $string;

    }

    #[Override]
    public function getMetadata(?string $key = null)
    {
        return $this->meta[$key] ?? null;
    }
}
