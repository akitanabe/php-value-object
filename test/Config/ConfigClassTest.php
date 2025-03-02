<?php

declare(strict_types=1);

use PhpValueObject\BaseModel;
use PhpValueObject\Config\ConfigClass;
use PhpValueObject\Exceptions\InheritableClassException;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PhpValueObject\Exceptions\UninitializedException;

#[ConfigClass(allowUninitializedProperty: false)]
class NotAllowUninitializedPropertyClassValue extends BaseModel
{
    public string $uninitialized;
}

#[ConfigClass(allowUninitializedProperty: true)]
class AllowInitializedPropertyValue extends BaseModel
{
    public string $uninitialized;
}


#[ConfigClass(allowMixedTypeProperty: true)]
class AllowMixedTypeClassValue extends BaseModel
{
    public mixed $mixed = 'mixed';
}

#[ConfigClass(allowMixedTypeProperty: false)]
class NotAllowMixedTypeClassValue extends BaseModel
{
    public mixed $mixed = 'mixed';
}

#[ConfigClass(allowNoneTypeProperty: true)]
class AllowNoneTypePropertyClassValue extends BaseModel
{
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

#[ConfigClass(allowNoneTypeProperty: false)]
class NotAllowNoneTypePropertyClassValue extends BaseModel
{
    // @phpstan-ignore missingType.property
    public $none = 'none';
}

#[ConfigClass(allowInheritableClass: false)]
class NotAllowInheritableClassValue extends BaseModel {}

#[ConfigClass(allowInheritableClass: true)]
class AllowInheritableClassValue extends BaseModel {}


class ConfigClassTest extends TestCase
{
    #[Test]
    #[DoesNotPerformAssertions]
    public function allowUnitinializedProperty(): void
    {
        AllowInitializedPropertyValue::fromArray();

    }

    #[Test]
    public function notAllowUninitializedProperty(): void
    {
        $this->expectException(UninitializedException::class);
        NotAllowUninitializedPropertyClassValue::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowMixedTypeProperty(): void
    {
        AllowMixedTypeClassValue::fromArray();

    }

    #[Test]
    public function notAllowMixedTypeProperty(): void
    {
        $this->expectException(TypeError::class);
        NotAllowMixedTypeClassValue::fromArray();
    }

    #[Test]
    #[DoesNotPerformAssertions]
    public function allowNoneTypeProperty(): void
    {
        AllowNoneTypePropertyClassValue::fromArray();

    }

    #[Test]
    public function notAllowNoneTypeProperty(): void
    {
        $this->expectException(TypeError::class);
        NotAllowNoneTypePropertyClassValue::fromArray();
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
