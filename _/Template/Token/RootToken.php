<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Template\Token;

use verfriemelt\wrapped\_\Template\TokenizerException;
use Override;

final class RootToken extends Token
{
    #[Override]
    public function setNext(Token $token): void
    {
        throw new TokenizerException('root token cannot have next');
    }

    #[Override]
    public function setPrevious(Token $token): void
    {
        throw new TokenizerException('root token cannot have previous');
    }
}
