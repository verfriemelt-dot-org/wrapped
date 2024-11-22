<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\psr7;

use Http\Psr7Test\RequestIntegrationTest;
use Override;
use verfriemelt\wrapped\_\HttpClient\Psr\Request;

class RequestTest extends RequestIntegrationTest
{
    #[Override]
    public function createSubject()
    {
        return new Request();
    }
}
