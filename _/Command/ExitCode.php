<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\Command;

enum ExitCode: int
{
    case Success = 0;
    case Error = 1;
}
