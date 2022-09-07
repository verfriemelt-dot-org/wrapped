<?php declare( strict_types = 1 );

namespace verfriemelt\wrapped\_\Formular\FormTypes;

class SelectItem
{

    public readonly string $name;
    public readonly string $value;

    public function __construct( string $name, string $value )
    {
        $this->name = $name;
        $this->value = $value;
    }

}
