# Yii2 Views & Templating

```php
namespace yii\web;

class View extends \yii\base\View
{
    public $title;           // Page title
    public $params = [];     // Shared params (breadcrumbs, etc.)

    // Asset registration
    public function registerMetaTag($options, $key = null);
    public function registerLinkTag($options, $key = null);
    public function registerCss($css, $options = [], $key = null);
    public function registerCssFile($url, $options = [], $key = null);
    public function registerJs($js, $position = self::POS_READY, $key = null);
    public function registerJsFile($url, $options = [], $key = null);

    // JS positions
    const POS_HEAD = 1;   // In <head>
    const POS_BEGIN = 2;  // Start of <body>
    const POS_END = 3;    // End of <body>
    const POS_READY = 4;  // jQuery ready
    const POS_LOAD = 5;   // Window load
}
```

## View Files
```php
<!-- views/site/index.php -->
<?php
use yii\helpers\Html;

$this->title = 'Page Title';
$this->params['breadcrumbs'][] = 'Home';
?>

<h1><?= Html::encode($this->title) ?></h1>
<p><?= Html::encode($message) ?></p>

<!-- Link -->
<?= Html::a('Click', ['controller/action', 'id' => 1]) ?>

<!-- Image -->
<?= Html::img('@web/images/logo.png', ['alt' => 'Logo']) ?>
```

## Layouts
```php
<!-- views/layouts/main.php -->
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html>
<head>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
    <?= $content ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
```

## Widgets
```php
<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'password')->passwordInput() ?>
    <?= Html::submitButton('Submit') ?>
<?php ActiveForm::end(); ?>
```

## Html Helper
```php
Html::encode($text);                    // XSS-safe output
Html::a($text, $url, $options);         // Link
Html::img($src, $options);              // Image
Html::tag($name, $content, $options);   // Generic tag
Html::beginTag($name, $options);        // Open tag
Html::endTag($name);                    // Close tag
```
