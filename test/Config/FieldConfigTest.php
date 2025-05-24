<?php

declare(strict_types=1);

namespace PhSculptis\Test\Config;

use PhSculptis\BaseModel;
use PhSculptis\Config\FieldConfig;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhSculptis\Exceptions\InvalidPropertyStateException;

class NotAllowUninitializedPropertyFieldModel extends BaseModel
{
    #[FieldConfig(allowUninitializedProperty: false)]
    public string $uninitialized;
}

class AllowUnInitializedPropertyFieldModel extends BaseModel
{
    #[FieldConfig(allowUninitializedProperty: true)]
    public string $uninitialized;
}



class AllowMixedTypeFieldModel extends BaseModel
{
    #[FieldConfig(allowMixedTypeProperty: true)]
    public mixed $mixed = 'mixed';
}


class NotAllowMixedTypeFieldModel extends BaseModel
{
    #[FieldConfig(allowMixedTypeProperty: false)]
    public mixed $mixed = 'mixed';
}

class AllowNoneTypePropertyFieldModel extends BaseModel
{
    #[FieldConfig(allowNoneTypeProperty: true)]
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

class NotAllowNoneTypePropertyFieldModel extends BaseModel
{
    #[FieldConfig(allowNoneTypeProperty: false)]
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

class FieldConfigTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowUnitinializedProperty(): void
    {
        AllowUnInitializedPropertyFieldModel::fromArray();

    }

    #[Test]
    public function notAllowUninitializedProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowUninitializedPropertyFieldModel::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty(): void
    {
        AllowMixedTypeFieldModel::fromArray();

    }

    #[Test]
    public function notAllowMixedTypeProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowMixedTypeFieldModel::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty(): void
    {
        AllowNoneTypePropertyFieldModel::fromArray();

    }

    #[Test]
    public function notAllowNoneTypeProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowNoneTypePropertyFieldModel::fromArray();
    }

}
