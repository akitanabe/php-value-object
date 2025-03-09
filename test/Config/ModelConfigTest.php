<?php

declare(strict_types=1);

namespace PhpValueObject\Test\Config;

use PhpValueObject\BaseModel;
use PhpValueObject\Config\ModelConfig;
use PhpValueObject\Exceptions\InheritableClassException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\InvalidPropertyStateException;

#[ModelConfig(allowUninitializedProperty: false)]
class NotAllowUninitializedPropertyClassModel extends BaseModel
{
    public string $uninitialized;
}

#[ModelConfig(allowUninitializedProperty: true)]
class AllowUnInitializedPropertyClassModel extends BaseModel
{
    public string $uninitialized;
}


#[ModelConfig(allowMixedTypeProperty: true)]
class AllowMixedTypeClassModel extends BaseModel
{
    public mixed $mixed = 'mixed';
}

#[ModelConfig(allowMixedTypeProperty: false)]
class NotAllowMixedTypeClassModel extends BaseModel
{
    public mixed $mixed = 'mixed';
}

#[ModelConfig(allowNoneTypeProperty: true)]
class AllowNoneTypePropertyClassModel extends BaseModel
{
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

#[ModelConfig(allowNoneTypeProperty: false)]
class NotAllowNoneTypePropertyClassModel extends BaseModel
{
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

#[ModelConfig(allowInheritableClass: false)]
class NotAllowInheritableClassValue extends BaseModel {}

#[ModelConfig(allowInheritableClass: true)]
class AllowInheritableClassValue extends BaseModel {}


class ModelConfigTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowUnitinializedProperty(): void
    {
        AllowUnInitializedPropertyClassModel::fromArray();

    }

    #[Test]
    public function notAllowUninitializedProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowUninitializedPropertyClassModel::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty(): void
    {
        AllowMixedTypeClassModel::fromArray();

    }

    #[Test]
    public function notAllowMixedTypeProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowMixedTypeClassModel::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty(): void
    {
        AllowNoneTypePropertyClassModel::fromArray();

    }

    #[Test]
    public function notAllowNoneTypeProperty(): void
    {
        $this->expectException(InvalidPropertyStateException::class);
        NotAllowNoneTypePropertyClassModel::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowInheritableClass(): void
    {
        AllowInheritableClassValue::fromArray();
    }

    #[Test]
    public function notAllowInheritableClass(): void
    {
        $this->expectException(InheritableClassException::class);
        NotAllowInheritableClassValue::fromArray();
    }


}
