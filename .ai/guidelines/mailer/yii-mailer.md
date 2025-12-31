# Yii2 Mailer

## Configuration
```php
// config/web.php
'components' => [
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        'viewPath' => '@app/mail',
        'useFileTransport' => false, // Set true for dev (saves to @runtime/mail)
        'transport' => [
            'class' => 'Swift_SmtpTransport',
            'host' => 'smtp.example.com',
            'username' => 'user',
            'password' => 'pass',
            'port' => '587',
            'encryption' => 'tls',
        ],
    ],
],
```

## Sending Email
```php
// Simple text email
Yii::$app->mailer->compose()
    ->setFrom('from@example.com')
    ->setTo('to@example.com')
    ->setSubject('Subject')
    ->setTextBody('Plain text content')
    ->send();

// HTML email with view template
Yii::$app->mailer->compose('contact/html', ['model' => $model])
    ->setFrom('from@example.com')
    ->setTo($model->email)
    ->setSubject('Contact Form')
    ->send();

// With attachment
Yii::$app->mailer->compose()
    ->setTo('to@example.com')
    ->setSubject('Report')
    ->attach('/path/to/file.pdf')
    ->attachContent($content, ['fileName' => 'report.pdf'])
    ->send();
```

## Email Views
```php
<!-- mail/contact/html.php -->
<p>Hello <?= $model->name ?>,</p>
<p>Thank you for contacting us.</p>

<!-- mail/contact/text.php -->
Hello <?= $model->name ?>,
Thank you for contacting us.
```

## Compose with Both HTML and Text
```php
Yii::$app->mailer->compose([
    'html' => 'contact/html',
    'text' => 'contact/text',
], ['model' => $model]);
```
