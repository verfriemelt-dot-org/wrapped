<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

final class VariableToken extends AbstractToken implements SimpleToken
{
    public function __construct(
        public readonly string $query,
        public readonly bool $raw = false
    ) {
    }
}
