# Yii2 Official Extensions

## Debug Toolbar
```php
// config/web.php (dev only)
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}
```

## Gii Code Generator
```php
// config/web.php (dev only)
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}
```

Access at: `/gii` (Model Generator, CRUD Generator, etc.)

## Common Extensions
```bash
composer require yiisoft/yii2-redis      # Redis cache/session
composer require yiisoft/yii2-queue      # Background jobs
composer require yiisoft/yii2-authclient # OAuth clients
composer require yiisoft/yii2-httpclient # HTTP client
composer require yiisoft/yii2-swiftmailer # Email
```

## Extension Configuration Pattern
```php
'components' => [
    'redis' => [
        'class' => 'yii\redis\Connection',
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 0,
    ],
],
```
