<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\_\DataModel\View;

use Exception;
use verfriemelt\wrapped\_\DataModel\DataModel;
use Override;

class ViewDataModel extends DataModel
{
    #[Override]
    public static function truncate(): void
    {
        throw new Exception('delete not allowed');
    }

    #[Override]
    public function delete(): static
    {
        throw new Exception('delete not allowed');
    }

    #[Override]
    public function save(): static
    {
        throw new Exception('update not allowed');
    }
}
