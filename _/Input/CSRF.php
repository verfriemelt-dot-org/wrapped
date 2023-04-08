<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Input;

use verfriemelt\wrapped\_\Session\Session;

class CSRF
{
    private ?\verfriemelt\wrapped\_\Session\Session $session = null;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function generateToken(string $contextName): string
    {
        if ($this->session->has($contextName)) {
            return $this->session->get($contextName);
        }

        $token = md5(uniqid((string) random_int(0, mt_getrandmax()), true));
        $this->session->set($contextName, $token);

        return $token;
    }

    public function validateToken(string $contextName, string $token): bool
    {
        if ($this->session->get($contextName) === $token) {
            $this->session->delete($contextName);
            return true;
        }

        return false;
    }
}
