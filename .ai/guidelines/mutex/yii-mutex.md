# Yii2 Mutex

## Configuration
```php
// config/web.php
'components' => [
    'mutex' => [
        'class' => 'yii\mutex\FileMutex', // or MysqlMutex, PgsqlMutex, RedisMutex
    ],
],
```

## Usage
```php
$mutex = Yii::$app->mutex;

// Acquire lock (returns immediately if can't acquire)
if ($mutex->acquire('my-lock')) {
    try {
        // Critical section
    } finally {
        $mutex->release('my-lock');
    }
}

// With timeout (wait up to 10 seconds)
if ($mutex->acquire('my-lock', 10)) {
    // ...
    $mutex->release('my-lock');
}
```

## Console Command Example
```php
class CronController extends Controller
{
    public function actionProcess()
    {
        if (!Yii::$app->mutex->acquire('cron-process', 0)) {
            $this->stdout("Already running\n");
            return ExitCode::OK;
        }

        try {
            // Process tasks...
        } finally {
            Yii::$app->mutex->release('cron-process');
        }

        return ExitCode::OK;
    }
}
```

## Available Backends
- `FileMutex` - File-based (default)
- `MysqlMutex` - MySQL GET_LOCK()
- `PgsqlMutex` - PostgreSQL advisory locks
- `RedisMutex` - Redis SETNX (via yii2-redis)
