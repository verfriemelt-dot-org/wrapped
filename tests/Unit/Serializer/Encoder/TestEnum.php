<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\Serializer\Encoder;

enum TestEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}
