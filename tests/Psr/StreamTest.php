<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\psr7;

use Http\Psr7Test\StreamIntegrationTest;
use Override;
use verfriemelt\wrapped\_\HttpClient\Psr\StreamFactory;

class StreamTest extends StreamIntegrationTest
{
    #[Override]
    public function createStream($data)
    {
        assert(\is_resource($data));
        return (new StreamFactory())->createStreamFromResource($data);
    }
}
