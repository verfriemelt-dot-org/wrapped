<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\psr7;

use Http\Psr7Test\RequestIntegrationTest;
use Override;
use verfriemelt\wrapped\_\HttpClient\Psr\ServerRequest;

class ServerRequestTest extends RequestIntegrationTest
{
    #[Override]
    public function createSubject()
    {
        return new ServerRequest();
    }
}
