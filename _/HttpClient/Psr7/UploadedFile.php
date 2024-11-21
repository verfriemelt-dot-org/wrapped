<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Override;
use RuntimeException;

final class UploadedFile implements UploadedFileInterface
{
    private const array UPLOAD_ERRORS = [
        \UPLOAD_ERR_CANT_WRITE,
        \UPLOAD_ERR_EXTENSION,
        \UPLOAD_ERR_FORM_SIZE,
        \UPLOAD_ERR_INI_SIZE,
        \UPLOAD_ERR_NO_FILE,
        \UPLOAD_ERR_NO_TMP_DIR,
        \UPLOAD_ERR_OK,
        \UPLOAD_ERR_PARTIAL,
    ];

    private bool $moved = false;

    public function __construct(
        private readonly StreamInterface $stream,
        private readonly int $errorCode,
        private readonly ?string $filename = null,
        private readonly ?string $mediaType = null,
    ) {
        assert(\in_array($this->errorCode, self::UPLOAD_ERRORS, true));
    }

    #[Override]
    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new RuntimeException('stream has moved to path');
        }

        return $this->stream;
    }

    #[Override]
    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('stream has moved to path');
        }

        $this->stream->rewind();
        \file_put_contents($targetPath, $this->stream->getContents());

        $this->moved = true;
    }

    #[Override]
    public function getSize(): ?int
    {
        return $this->stream->getSize();
    }

    #[Override]
    public function getError(): int
    {
        return $this->errorCode;
    }

    #[Override]
    public function getClientFilename(): ?string
    {
        return $this->filename;
    }

    #[Override]
    public function getClientMediaType(): ?string
    {
        return $this->mediaType;
    }
}
