<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Input;

use verfriemelt\wrapped\_\Http\Request\Request;

class CSRF
{
    public function __construct(
        private readonly Request $request
    ) {}

    public function generateToken(string $contextName): string
    {
        if ($this->request->getSession()->has($contextName)) {
            return $this->request->getSession()->get($contextName);
        }

        $token = md5(uniqid((string) random_int(0, mt_getrandmax()), true));
        $this->request->getSession()->set($contextName, $token);

        return $token;
    }

    public function validateToken(string $contextName, string $token): bool
    {
        if ($this->request->getSession()->get($contextName) === $token) {
            $this->request->getSession()->delete($contextName);
            return true;
        }

        return false;
    }
}
