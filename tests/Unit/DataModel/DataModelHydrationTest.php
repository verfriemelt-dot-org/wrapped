<?php

declare(strict_types=1);

namespace verfriemelt\wrapped\Tests\Unit\DataModel;

use PHPUnit\Framework\TestCase;
use verfriemelt\wrapped\_\DataModel\DataModel;

class NonNullableIdModel extends DataModel
{
    protected int $id;

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

class DataModelHydrationTest extends TestCase
{
    public function test_hydration_on_non_nullable_properties(): void
    {
        $model = new NonNullableIdModel();
        $model->initData(['id' => null]);

        $model = new NonNullableIdModel();
        $model->initData(['id' => 1]);

        static::assertSame(1, $model->getId());
    }

    public function test_persisted_object(): void
    {
        static::assertFalse((new NonNullableIdModel())->isPersisted());
    }

    public function test_dirty_object(): void
    {
        $model = new NonNullableIdModel();
        $model->initData(['id' => 1]);

        static::assertFalse($model->isDirty());

        $model->setId(2);

        static::assertTrue($model->isDirty());
    }
}
