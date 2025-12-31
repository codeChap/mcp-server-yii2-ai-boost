# Yii2 Logging

## Log Methods
```php
Yii::error('Message', 'category');    // Errors
Yii::warning('Message', 'category');  // Warnings
Yii::info('Message', 'category');     // Info
Yii::trace('Message', 'category');    // Debug (only in YII_DEBUG)
```

## Configuration
```php
// config/web.php
'components' => [
    'log' => [
        'targets' => [
            // File target
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
                'categories' => ['app\*'],
                'logFile' => '@runtime/logs/app.log',
            ],
            // Database target
            [
                'class' => 'yii\log\DbTarget',
                'levels' => ['error'],
            ],
            // Email target
            [
                'class' => 'yii\log\EmailTarget',
                'levels' => ['error'],
                'message' => [
                    'to' => 'admin@example.com',
                    'subject' => 'Error at site',
                ],
            ],
        ],
    ],
],
```

## Categories
```php
// Log with category
Yii::info('User logged in', 'app\auth');
Yii::error('Payment failed', 'app\payment');

// Filter by category in target
'categories' => ['app\*'],           // All app categories
'except' => ['app\payment\debug'],   // Exclude specific
```

## Profiling
```php
Yii::beginProfile('blockName');
// ... code to profile ...
Yii::endProfile('blockName');
```
