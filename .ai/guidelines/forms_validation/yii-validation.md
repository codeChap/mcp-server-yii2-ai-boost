# Yii2 Validation

## Common Validators
```php
public function rules()
{
    return [
        // Required
        [['username', 'email'], 'required'],
        
        // Type validators
        ['age', 'integer'],
        ['price', 'number'],
        ['active', 'boolean'],
        ['name', 'string', 'min' => 2, 'max' => 255],
        
        // Format validators
        ['email', 'email'],
        ['website', 'url'],
        ['birth_date', 'date', 'format' => 'php:Y-m-d'],
        
        // Comparison
        ['password_repeat', 'compare', 'compareAttribute' => 'password'],
        ['age', 'compare', 'compareValue' => 18, 'operator' => '>='],
        
        // Range
        ['status', 'in', 'range' => [1, 2, 3]],
        
        // Pattern
        ['phone', 'match', 'pattern' => '/^\+?[0-9]{10,15}$/'],
        
        // Database
        ['email', 'unique'],
        ['category_id', 'exist', 'targetClass' => Category::class],
        
        // Safe (mass assignment only)
        ['description', 'safe'],
        
        // Custom
        ['field', 'validateCustom'],
        
        // Conditional
        ['phone', 'required', 'when' => function($model) {
            return $model->contact_method === 'phone';
        }],
    ];
}
```

## Custom Validator
```php
public function validateCustom($attribute, $params)
{
    if ($this->$attribute === 'invalid') {
        $this->addError($attribute, 'Value is invalid.');
    }
}
```

## Validation Usage
```php
$model = new User();
$model->load(Yii::$app->request->post());

if ($model->validate()) {
    $model->save(false); // Skip validation (already done)
}

// Get errors
$model->getErrors();           // All errors
$model->getFirstErrors();      // First error per attribute
$model->hasErrors('email');    // Check specific attribute
```

## Scenarios
```php
public function scenarios()
{
    return [
        'register' => ['username', 'email', 'password'],
        'update' => ['username', 'email'],
    ];
}

$model->scenario = 'register';
```
