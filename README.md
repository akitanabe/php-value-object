# PhSculptis

PHP でエレガントなデータバリデーションとモデリングを提供するライブラリです。Python の Pydantic にインスパイアされ、型安全性とバリデーション機能を組み合わせて、美しく堅牢なデータモデルを簡単に構築できます。

## ✨ 特徴

### 🛡️ 型安全性の強制
- 継承可能クラスの禁止(final キーワードの必須化)
- 型が指定されていないプロパティの禁止
- 初期化されていないプロパティの禁止
- mixed 型の禁止

### 🚀 自動化機能
- 名前付き引数によるプロパティ自動入力
- 親コンストラクタを呼び出すことによる自動入力
- clone 時のオブジェクト自動 clone

### 🎯 柔軟性
- Attribute による制約の緩和
- カスタムバリデーションの実装
- 豊富な組み込みバリデーター

## 📦 インストール

```bash
composer require akitanabe/phsculptis
```

## 🚀 基本的な使い方

コンストラクタに渡した名前付き引数と同じ名前のプロパティに自動的に値が設定されます。

```php
<?php

use PhSculptis\BaseModel;

final class UserModel extends BaseModel
{
    public string $name;
    public int $age;
    public string $email;
    public bool $isActive = true;
}

$user = new UserModel(
    name: 'John Doe',
    age: 30,
    email: 'john@example.com'
);

echo $user->name; // 'John Doe'
echo $user->age; // 30
echo $user->email; // 'john@example.com'
echo $user->isActive; // true (デフォルト値)
```

## カスタムコンストラクタ

独自のコンストラクタロジックが必要な場合は、親コンストラクタを呼び出して自動入力機能を利用できます。

```php
final class ProductModel extends BaseModel
{
    public string $name;
    public int $price;
    public string $sku;
    public bool $inStock;

    public function __construct(
        string $name,
        int $price,
        ?string $sku = null
    ) {
        // SKUが指定されていない場合は自動生成
        $generatedSku = $sku ?? strtoupper(substr(md5($name), 0, 8));
        
        parent::__construct(
            name: $name,
            price: $price,
            sku: $generatedSku,
            inStock: $price > 0
        );
    }
}

$product = new ProductModel(
    name: 'Laptop',
    price: 999
);
// SKUは自動生成され、inStockはtrueになる
```

## 制約の緩和

各制約は 対応する Attribute を付加すれば緩和できます。

```
// 初期化していないプロパティを許可する
#[AllowUninitializedProperty]
final class UninitializedValue extends BaseValueObject
{
    public string $uninitialized;
}

// OK インスタンス化してもエラーは発生しない。
$value = new UninitializedValue();

// 初期化されていないのでFatal error
$value->uninitialized;

```

| Attribute                  | 緩和する制約                             |
| -------------------------- | ---------------------------------------- |
| AllowInheritableClass      | 継承(final キーワードが不要)を許可       |
| AllowUninitializedProperty | 初期化されていないプロパティを許可       |
| AllowNoneTypeProperty      | 型が指定されていないプロパティを許可     |
| AllowMixedTypeProperty     | mixed 型が指定されているプロパティを許可 |

## バリデーション

プロパティにバリデーター Attribute を指定することで、データの整合性を保証できます。

```php
use PhSculptis\Validators\StringValidator;
use PhSculptis\Validators\NumericValidator;

final class RegisterUserModel extends BaseModel
{
    #[StringValidator(minLength: 3, maxLength: 50)]
    public string $username;
    
    #[NumericValidator(min: 18, max: 120)]
    public int $age;
    
    public string $email;
}

// ✅ 正常なケース
$user = new RegisterUserModel(
    username: 'john_doe',
    age: 25,
    email: 'john@example.com'
);

// ❌ バリデーションエラー - 文字数不足
$user = new RegisterUserModel(
    username: 'jo', // StringValidatorによりエラー
    age: 25,
    email: 'john@example.com'
);
```

### 現在用意されているバリデーション

| Attribute               | バリデーション             |
| ----------------------- | -------------------------- |
| NotEmptyStringValidator | 空文字のバリデーション     |
| AlphaNumericValidator   | 英数字文字のバリデーション |

## カスタムバリデーション

Validation\Validatable インターフェースを実装したアトリビュートクラスを作成してください。

```php
use Attribute;
use PhSculptis\Validation\Validatable;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class CustomValidator implements Validatable
{
    public function validate(mixed $value): bool
    {
        if($value === "custom"){
            return true;
        }

        return false;
    }

    public function errorMessage(): string
    {
        return "custom error";
    }
}

final class CustomValidationValue extends BaseModel
{
    #[CustomValidator]
    public string $string;
}

// OK
$customValue = new CustomValidationValue(string: "custom");

// エラー
$customValue = new CustomValidationValue(string: "not-custom");
```

## 使用例：API リクエストモデル

```php
use PhSculptis\BaseModel;
use PhSculptis\Validators\StringValidator;
use PhSculptis\Validators\NumericValidator;

final class CreateUserRequest extends BaseModel
{
    #[StringValidator(minLength: 2, maxLength: 50)]
    public string $name;
    
    #[StringValidator(pattern: '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')]
    public string $email;
    
    #[NumericValidator(min: 18)]
    public int $age;
    
    public bool $newsletter = false;
}

// APIエンドポイントでの使用例
function createUser(array $requestData): array
{
    try {
        $request = new CreateUserRequest(...$requestData);
        
        // バリデーションが成功した場合のみここに到達
        $user = saveUser($request);
        
        return ['success' => true, 'user' => $user];
    } catch (ValidationException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```

このライブラリを使用することで、型安全でバリデーション機能を持つデータモデルを簡潔に定義でき、APIやフォーム処理などで堅牢なデータハンドリングが可能になります。
