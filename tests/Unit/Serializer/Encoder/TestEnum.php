<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\tests\Unit\Serializer\Encoder;

enum TestEnum: string
{
    case Foo = 'foo';
    case Bar = 'bar';
}
