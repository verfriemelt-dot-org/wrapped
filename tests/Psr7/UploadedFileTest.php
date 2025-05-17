<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Psr7;

use Http\Psr7Test\UploadedFileIntegrationTest;
use verfriemelt\wrapped\_\HttpClient\Psr\StreamFactory;
use verfriemelt\wrapped\_\HttpClient\Psr\UploadedFile;
use Override;

class UploadedFileTest extends UploadedFileIntegrationTest
{
    #[Override]
    public function createSubject()
    {
        $stream = (new StreamFactory())->createStream('testing');

        return new UploadedFile($stream, UPLOAD_ERR_OK, 'filename.txt', 'text/plain');
    }
}
