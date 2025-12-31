# Yii2 Internationalization (I18N)

## Message Translation
```php
// Basic translation
Yii::t('app', 'Hello');

// With parameters
Yii::t('app', 'Welcome, {name}', ['name' => 'John']);

// Pluralization
Yii::t('app', '{n, plural, =0{No posts} =1{One post} other{# posts}}', ['n' => $count]);
```

## Configuration
```php
// config/web.php
'components' => [
    'i18n' => [
        'translations' => [
            'app*' => [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@app/messages',
            ],
        ],
    ],
],
```

## Message Files
```php
// messages/de/app.php
return [
    'Hello' => 'Hallo',
    'Welcome, {name}' => 'Willkommen, {name}',
];
```

## Formatter
```php
$formatter = Yii::$app->formatter;

// Date/Time
$formatter->asDate($timestamp);           // Jan 1, 2024
$formatter->asDatetime($timestamp);       // Jan 1, 2024 12:00 PM
$formatter->asRelativeTime($timestamp);   // 2 hours ago

// Numbers
$formatter->asDecimal(1234.56);           // 1,234.56
$formatter->asCurrency(1234.56, 'USD');   // $1,234.56
$formatter->asPercent(0.75);              // 75%

// Other
$formatter->asBoolean(true);              // Yes
$formatter->asEmail('test@example.com');  // Clickable link
$formatter->asText($html);                // Strip tags
```

## Set Locale
```php
Yii::$app->language = 'de-DE';
Yii::$app->formatter->locale = 'de-DE';
```
