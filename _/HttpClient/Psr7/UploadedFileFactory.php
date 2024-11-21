<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\HttpClient\Psr7;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Override;

final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    #[Override]
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = \UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null,
    ): UploadedFileInterface {

        return new UploadedFile(
            $stream,
            $error,
            $clientFilename,
            $clientMediaType,
        );
    }
}
