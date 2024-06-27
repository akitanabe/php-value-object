# php-value-object

PHP で DDD を実装する際に 以下の制約を満たす ValueObject を作成できます。

- 継承可能クラスの禁止(final キーワードの必須化)
- 型が指定されていないプロパティの禁止
- 初期化されていないプロパティの禁止
- mixed 型の禁止

また以下の機能を付加しています。

- 名前付き引数によるプロパティ自動入力
- 親コンストラクタを呼び出すことによる自動入力
- Attribute による制約の緩和
- Attribute によるバリデーション
- clone 時のオブジェクト自動 clone

## 基本的な使い方

コンストラクタに渡した名前付き引数と同じ名前のプロパティに入力されます。

存在しないプロパティ名を渡しても入力されません。

```
<?php

use Akitanabe\PhpValueObject\BaseValueObject;

final class BasicValue extends BaseValueObject
{
    public string $stringVal;
    public int $intVal;
    public float $floatVal = 0.01;
    public bool $boolVal = false;
}

$value = new BasicValue(
            stringVal: 'string',
            intVal: 1,
            floatVal: 0.1,
         );

$value->stringVal === 'string'; // true
$value->intVal === 1; // true
$value->floatVal === 0.1; // true
$value->boolVal === false; // true


```

## 子クラスのコンストラクタを使いたい場合

親クラスのコンストラクタに ...func_get_args() を使って渡します。

コンストラクタ引数以外にプロパティがある場合はコンストラクタ引数に名前付き引数で渡します。

```
final class ChildCustomValue extends BaseValueObject
{
    public bool $boolVal;

    public function __construct(
        public string $stringVal,
        public int $intVal,
        public float $floatVal = 0.01,
    ){
        // コンストラクタ引数以外に入力値が存在する場合は名前付き引数で指定
        parent::__construct(...func_get_args(), boolVal: true);

        // 順番が同じなら直接書いてもOK
        parent::__construct($stringVal, $intVal, $floatVal, boolVal: true);

        // 名前付き引数で入力してもOK
        parent::__construct(
            stringVal: $stringVal,
            intVal: $intVal,
            floatVal: $floatVal,
            boolVal: true
        );
    }
}


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

各プロパティの Attribute に Validator を指定してください。

```
use Akitanabe\PhpValueObject\Attribute\Validator\NotEmptyStringValidator;

final class ValidationValue extends BaseValueObject
{
    #[NotEmptyStringValidator]
    public string $string;
}

// OK
$value = new $ValidationValue(string: "string");

// NG
$value = new $ValidationValue(string: "");

```

### 現在用意されているバリデーション

| Attribute               | バリデーション             |
| ----------------------- | -------------------------- |
| NotEmptyStringValidator | 空文字のバリデーション     |
| AlphaNumericValidator   | 英数字文字のバリデーション |

## カスタムバリデーション

Validation\Validatable インターフェースを実装したアトリビュートクラスを作成してください。

```
use Attribute;
use Akitanabe\PhpValueObject\Validation\Validatable;

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

final class CustomValidationValue extends BaseValueObject
{
    #[CustomValidator]
    public string $string;
}

// OK
$customValue = new CustomValidationValue(string: "custom");

// エラー
$customValue = new CustomValidationValue(string: "not-custom");


```
