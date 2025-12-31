# Yii2 Application

## Web Application Entry Point
```php
// web/index.php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';
(new yii\web\Application($config))->run();
```

## Console Application Entry Point
```php
// yii (console script)
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/config/console.php';
$exitCode = (new yii\console\Application($config))->run();
exit($exitCode);
```

## Application Properties
```php
Yii::$app->id;           // Application ID
Yii::$app->name;         // Application name
Yii::$app->basePath;     // Base path
Yii::$app->language;     // Current language
Yii::$app->timeZone;     // Timezone
Yii::$app->params;       // Custom parameters
```

## Lifecycle
```php
// Bootstrap
'bootstrap' => ['log', 'myComponent'],

// Events
Application::EVENT_BEFORE_REQUEST
Application::EVENT_AFTER_REQUEST
Application::EVENT_BEFORE_ACTION
Application::EVENT_AFTER_ACTION
```

## Path Aliases
```php
Yii::setAlias('@uploads', '@app/web/uploads');
$path = Yii::getAlias('@uploads/file.jpg');

// Built-in aliases
// @app, @vendor, @runtime, @webroot, @web
```
