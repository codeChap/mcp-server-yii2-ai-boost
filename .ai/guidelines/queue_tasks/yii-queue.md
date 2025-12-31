# Yii2 Queue (yii2-queue extension)

## Installation
```bash
composer require yiisoft/yii2-queue
```

## Configuration
```php
// config/web.php
'components' => [
    'queue' => [
        'class' => \yii\queue\db\Queue::class, // or Redis, AMQP, etc.
        'db' => 'db',
        'tableName' => '{{%queue}}',
        'channel' => 'default',
        'as log' => \yii\queue\LogBehavior::class,
    ],
],
```

## Create a Job
```php
class DownloadJob extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $url;
    public $file;

    public function execute($queue)
    {
        file_put_contents($this->file, file_get_contents($this->url));
    }
}
```

## Push Jobs
```php
// Push to queue
$id = Yii::$app->queue->push(new DownloadJob([
    'url' => 'https://example.com/file.pdf',
    'file' => '/path/to/save.pdf',
]));

// Push with delay (seconds)
Yii::$app->queue->delay(60)->push(new SendEmailJob([...]));
```

## Run Worker
```bash
php yii queue/listen    # Daemon mode
php yii queue/run       # Process and exit
```

## Available Backends
- `yii\queue\db\Queue` - Database
- `yii\queue\redis\Queue` - Redis
- `yii\queue\amqp\Queue` - RabbitMQ
- `yii\queue\beanstalk\Queue` - Beanstalkd
