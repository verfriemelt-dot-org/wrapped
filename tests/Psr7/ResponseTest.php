<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\psr7;

use Http\Psr7Test\ResponseIntegrationTest;
use verfriemelt\wrapped\_\HttpClient\Psr7\Response;
use Override;

class ResponseTest extends ResponseIntegrationTest
{
    #[Override]
    public function createSubject()
    {
        return new Response();
    }
}
