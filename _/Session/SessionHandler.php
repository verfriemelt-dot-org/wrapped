<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Session;

interface SessionHandler
{
    public function set(string $name, mixed $value): static;

    public function get(string $name, mixed $default = null): mixed;

    public function has(string $name): bool;

    public function delete(string $name): static;

    public function destroy(): void;
}
