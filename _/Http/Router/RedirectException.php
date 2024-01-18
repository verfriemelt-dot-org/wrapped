<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Http\Router;

use RuntimeException;
use verfriemelt\wrapped\_\Http\Response\Redirect;

class RedirectException extends RuntimeException
{
    protected Redirect $redirect;

    public function __construct(Redirect $redirect)
    {
        parent::__construct();
        $this->redirect = $redirect;
    }

    public function getRedirect(): Redirect
    {
        return $this->redirect;
    }
}
