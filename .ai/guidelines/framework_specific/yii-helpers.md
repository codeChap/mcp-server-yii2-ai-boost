# Yii2 Helper Classes

## ArrayHelper
```php
use yii\helpers\ArrayHelper;

// Get value with dot notation
ArrayHelper::getValue($array, 'user.name', 'default');

// Build key-value map from objects/arrays
ArrayHelper::map($models, 'id', 'name');
// Result: [1 => 'John', 2 => 'Jane']

// Index array by key
ArrayHelper::index($models, 'id');

// Get column values
ArrayHelper::getColumn($models, 'name');

// Merge arrays recursively
ArrayHelper::merge($array1, $array2);

// Check if key exists
ArrayHelper::keyExists('key', $array);

// Remove element
ArrayHelper::remove($array, 'key');
```

## Url Helper
```php
use yii\helpers\Url;

// Create URL
Url::to(['site/index']);                    // /site/index
Url::to(['post/view', 'id' => 1]);          // /post/view?id=1
Url::to(['post/view', 'id' => 1], true);    // Absolute URL
Url::to(['post/view', 'id' => 1], 'https'); // Force HTTPS

// Current URL
Url::current();
Url::current(['page' => 2]); // Add/modify params

// Home URL
Url::home();
```

## Html Helper
```php
use yii\helpers\Html;

Html::encode($text);                         // XSS-safe
Html::a('Link', ['site/index']);             // Anchor tag
Html::img('@web/logo.png');                  // Image tag
Html::tag('div', 'content', ['class' => 'box']);
Html::ul(['item1', 'item2']);                // Unordered list
```

## Json Helper
```php
use yii\helpers\Json;

Json::encode($data);
Json::decode($json);
Json::htmlEncode($data); // Safe for embedding in HTML
```
