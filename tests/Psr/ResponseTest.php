<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\psr7;

use Http\Psr7Test\ResponseIntegrationTest;
use verfriemelt\wrapped\_\HttpClient\Psr\Response;
use Override;

class ResponseTest extends ResponseIntegrationTest
{
    #[Override]
    public function createSubject()
    {
        return new Response();
    }
}
