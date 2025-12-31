# Yii2 2.0.45 Framework Guidelines

## Application Structure

### Templates
- **Basic** (`yii2-app-basic`): Single-tier, all files in one directory
- **Advanced** (`yii2-app-advanced`): Separated frontend, backend, common, console
- **Modular**: Custom structure with isolated modules

### Directory Layout
```
app/
├── config/           # web.php, console.php, params.php
├── controllers/      # Web controllers
├── models/           # AR models and form models
├── views/            # View templates
├── web/              # Public root (index.php, assets/)
├── commands/         # Console commands
├── components/       # Custom components
├── migrations/       # Database migrations
├── modules/          # Application modules
└── runtime/          # Generated files, logs, cache
```

### Namespaces
- Application: `app\*`
- Module: `app\modules\{name}\*`
- Component: `app\components\*`

---

## Controllers

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['login'], 'allow' => true],
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
```

### Request/Response
```php
$request = Yii::$app->request;
$id = $request->get('id');
$data = $request->post('User');

throw new NotFoundHttpException('Not found');
throw new ForbiddenHttpException('Access denied');
```

---

## Models

### Active Record
```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['email', 'email'],
            ['username', 'string', 'max' => 255],
            ['username', 'unique'],
            ['status', 'in', 'range' => [1, 2, 3]],
        ];
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['user_id' => 'id']);
    }

    public function getPosts()
    {
        return $this->hasMany(Post::class, ['user_id' => 'id']);
    }
}
```

### Common Validators
```php
['field', 'required']
['field', 'string', 'max' => 255]
['field', 'integer']
['email', 'email']
['field', 'unique']
['field', 'in', 'range' => [1, 2, 3]]
['date', 'date', 'format' => 'php:Y-m-d']
['field', 'safe']
```

### Scenarios
```php
public function scenarios()
{
    return [
        'create' => ['username', 'email', 'password'],
        'update' => ['username', 'email'],
    ];
}

$model->scenario = 'create';
```

### Form Models
```php
class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
        ];
    }
}
```

---

## Views

### Rendering
```php
// Controller
return $this->render('view', ['data' => $data]);
return $this->renderPartial('_partial', ['data' => $data]);

// View
<?= Html::encode($userInput) ?>
<?= HtmlPurifier::process($richText) ?>
```

### Layouts
```php
<!-- views/layouts/main.php -->
<?= $content ?>  <!-- Content renders here -->
```

### Widgets
```php
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_item',
]) ?>

<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'username') ?>
<?php ActiveForm::end(); ?>
```

### Assets
```php
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = ['css/site.css'];
    public $js = ['js/site.js'];
    public $depends = ['yii\web\YiiAsset'];
}
```

---

## Components

### Registration
```php
// config/web.php
'components' => [
    'myService' => [
        'class' => 'app\components\MyService',
        'config' => 'value',
    ],
],

// Usage
Yii::$app->myService->doSomething();
```

### Custom Component
```php
class MyService extends Component
{
    public $config;

    public function init()
    {
        parent::init();
    }
}
```

### DI Container
```php
Yii::$container->setSingleton('myService', ['class' => MyService::class]);
Yii::$container->set('myService', ['class' => MyService::class]);
```

---

## Security

### CSRF
Enabled by default for POST/PUT/DELETE. Disable per-controller:
```php
public $enableCsrfValidation = false;
```

### SQL Injection Prevention
```php
// SAFE - always use parameter binding
User::find()->where(['username' => $username])->all();

// UNSAFE - never concatenate
User::find()->where("username = '$username'")->all();
```

### XSS Prevention
```php
<?= Html::encode($userInput) ?>
<?= HtmlPurifier::process($richText) ?>
```

### Auth
```php
Yii::$app->user->login($user);
Yii::$app->user->logout();
Yii::$app->user->isGuest;
Yii::$app->user->identity;
Yii::$app->user->can('permission');
```

---

## Performance

### Eager Loading
```php
// BAD - N+1 queries
$users = User::find()->all();
foreach ($users as $user) {
    echo $user->profile->name;
}

// GOOD - eager load
$users = User::find()->with('profile')->all();
```

### Query Optimization
```php
User::find()
    ->select(['id', 'username'])
    ->asArray()
    ->limit(10)
    ->all();
```

### Caching
```php
$data = Yii::$app->cache->getOrSet('key', function() {
    return expensiveOperation();
}, 3600);

// Query caching
User::find()->cache(3600)->all();
```

---

## Console Commands

```php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

class MyController extends Controller
{
    public function actionIndex($arg = 'default')
    {
        $this->stdout("Output\n", 32);  // Green
        return ExitCode::OK;
    }
}
```

---

## Migrations

```php
class m210101_000000_create_user extends Migration
{
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->unique(),
            'created_at' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
```

---

## Anti-Patterns

```php
// ❌ Echo in controllers
echo "Hello";
// ✓ Return response
return $this->render('view');

// ❌ Business logic in views
<?php if($post->author_id == Yii::$app->user->id): ?>
// ✓ Filter in controller/model

// ❌ Skip validation
$user->save();
// ✓ Always validate
if ($user->validate() && $user->save()) {}

// ❌ Hardcode values
$email = 'admin@example.com';
// ✓ Use params
$email = Yii::$app->params['adminEmail'];

// ❌ Raw SQL with variables
"SELECT * FROM user WHERE id=$id"
// ✓ Parameter binding
User::findOne($id);
```

---

## Key Principles

1. Always validate input via model rules
2. Use eager loading to avoid N+1 queries
3. Separate concerns: Controllers → Models → Views
4. Encode all output (XSS prevention)
5. Use parameter binding (SQL injection prevention)
6. Cache strategically
7. Use params for configuration values
