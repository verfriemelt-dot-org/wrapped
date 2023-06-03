<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\v2\Token;

final class Conditional extends AbstractToken implements SimpleToken
{
    public function __construct(
        public readonly string $condition,
        public readonly TokenInterface $consequent
    ) {
    }
}
