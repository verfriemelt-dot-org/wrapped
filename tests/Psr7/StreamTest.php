<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\psr7;

use Http\Psr7Test\StreamIntegrationTest;
use Override;
use verfriemelt\wrapped\_\HttpClient\Psr7\StreamFactory;

class StreamTest extends StreamIntegrationTest
{
    #[Override]
    public function createStream($data)
    {
        assert(\is_resource($data));
        return (new StreamFactory())->createStreamFromResource($data);
    }
}
