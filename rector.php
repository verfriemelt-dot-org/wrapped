<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php56\Rector\FunctionLike\AddDefaultValueForUndefinedVariableRector;
use Rector\Php71\Rector\FuncCall\CountOnNullRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {

    $rectorConfig->paths([
        '_',
        'tests',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
    ]);

    $rectorConfig->skip([
        AddDefaultValueForUndefinedVariableRector::class,

        // this is a type error on recent versions
        CountOnNullRector::class,

        ClassPropertyAssignToConstructorPromotionRector::class,
    ]);
};

