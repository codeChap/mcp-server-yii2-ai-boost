# Yii2 2.0.45 Framework Guidelines

## Table of Contents

1. [Application Structure](#application-structure)
2. [Controllers](#controllers)
3. [Models](#models)
4. [Views](#views)
5. [Components](#components)
6. [Security](#security)
7. [Performance](#performance)
8. [Console Commands](#console-commands)
9. [Common Patterns](#common-patterns)
10. [Anti-Patterns](#anti-patterns)

---

## Application Structure

### Application Types

Yii2 comes with different template types:

- **Basic Template** (`yii2-app-basic`): Single-tier application with all files in one directory
- **Advanced Template** (`yii2-app-advanced`): Separated frontend, backend, common, and console tiers
- **Modular Structure**: Custom structure with isolated modules (example: StackChap)

### Directory Organization

Standard directory structure:

```
app/
├── config/              # Configuration files
│   ├── web.php         # Web application config
│   ├── console.php     # Console application config
│   └── params.php      # Application parameters
├── controllers/        # Web controllers
├── models/             # Database models and form models
├── views/              # View templates
├── web/                # Web root (publicly accessible)
│   ├── index.php       # Entry point
│   ├── assets/         # Published asset bundles
│   └── css, js, images/
├── commands/           # Console commands (optional)
├── components/         # Custom application components
├── migrations/         # Database migrations
├── modules/            # Application modules (optional)
└── runtime/            # Generated files, logs, cache
```

### Namespace Conventions

- **Application namespace**: `app\*` or custom namespace configured in config
- **Module namespace**: `app\modules\{moduleName}\*`
- **Component namespace**: `app\components\*`
- **Model namespace**: `app\models\*`
- **Controller namespace**: `app\controllers\*`

### Bootstrap Process

Yii2 application initialization order:

1. `index.php` or `yii` console script loads Yii framework
2. Environment-specific configs loaded
3. Components registered and initialized
4. Bootstrap classes instantiated (via `$config['bootstrap']`)
5. Event listeners attached
6. Router resolves request to controller/action
7. Filters applied (behaviors)
8. Action executed
9. Response rendered

---

## Controllers

### Controller Basics

Controllers extend `yii\web\Controller` or `yii\console\Controller`:

```php
namespace app\controllers;

use yii\web\Controller;

class SiteController extends Controller
{
    // Action methods follow actionMethodName pattern
    public function actionIndex()
    {
        return $this->render('index'); // Renders views/site/index.php
    }
}
```

### Action Methods

- **Naming**: `actionMethodName()` - action names are camelCaseToKebab-case (actionViewUser → view-user)
- **Parameters**: URL parameters become action method parameters
- **Return Types**:
  - String (HTML): `return $this->render('view', ['data' => $data]);`
  - Response object: `return new Response(...);`
  - Array (JSON): Automatically converted to JSON response
  - Void: Empty response

### Request/Response Handling

```php
// Access request
$request = Yii::$app->request;
$get = $request->get('id');                    // $_GET['id']
$post = $request->post('User');                // $_POST['User']
$param = $request->getQueryParam('page', 1);   // With default value

// Access response
$response = Yii::$app->response;
$response->setStatusCode(404);
$response->headers->add('Custom-Header', 'value');
return $response;
```

### Filters and Behaviors

Filters are attached via `behaviors()` method:

```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'actions' => ['login', 'signup'],
                    'allow' => true,
                ],
                [
                    'allow' => true,
                    'roles' => ['@'],  // Authenticated users
                ],
            ],
        ],
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['POST'],
                'create' => ['GET', 'POST'],
            ],
        ],
    ];
}
```

### Error Handling

Exceptions are caught and converted to error pages:

```php
// Return 404
throw new NotFoundHttpException('Page not found');

// Return 403 (forbidden)
throw new ForbiddenHttpException('Access denied');

// Return generic error
throw new \Exception('Something went wrong');
```

---

## Models

### Active Record Pattern

Active Record models extend `yii\db\ActiveRecord`:

```php
namespace app\models;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    /**
     * Define table name
     */
    public static function tableName()
    {
        return '{{%user}}';  // {{%}} handles table prefix
    }

    /**
     * Define validation rules
     */
    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['email', 'email'],
            ['username', 'string', 'min' => 3, 'max' => 255],
            ['username', 'unique'],
            ['status', 'in', 'range' => [1, 2, 3]],
            ['created_at', 'safe'],
        ];
    }

    /**
     * Define relations
     */
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

### Validation Rules

Common validation rules:

```php
['field', 'required']                                    // Required
['field', 'string', 'max' => 255]                       // String with max length
['field', 'integer']                                    // Integer
['email', 'email']                                      // Email format
['field', 'unique']                                     // Unique in database
['field', 'unique', 'targetAttribute' => ['email']]    // Unique on another field
['field', 'in', 'range' => [1, 2, 3]]                  // From a list
['date', 'date', 'format' => 'php:Y-m-d']              // Date format
['field', 'safe']                                      // Declare safe for mass assignment
[['field1', 'field2'], 'required', 'on' => 'insert']   // Required only on insert
```

### Scenarios

Scenarios allow different validation rules for different contexts:

```php
public function scenarios()
{
    return [
        'default' => ['username', 'email', 'password'],
        'create' => ['username', 'email', 'password', 'confirm_password'],
        'update' => ['username', 'email'],
    ];
}

// Usage
$model = new User();
$model->scenario = 'create';
$model->load(Yii::$app->request->post());
if ($model->validate()) {
    // Validation passed
}
```

### Relations

Define relationships between models:

```php
// One-to-many relation (User has many Posts)
public function getPosts()
{
    return $this->hasMany(Post::class, ['user_id' => 'id']);
}

// Many-to-one relation (Post belongs to User)
public function getAuthor()
{
    return $this->hasOne(User::class, ['id' => 'user_id']);
}

// Many-to-many relation (via junction table)
public function getTags()
{
    return $this->hasMany(Tag::class, ['id' => 'tag_id'])
        ->viaTable('post_tag', ['post_id' => 'id']);
}

// Usage (lazy loading)
$user = User::findOne(1);
$posts = $user->posts;  // Executes additional query

// Usage (eager loading - recommended!)
$users = User::find()
    ->with('posts', 'profile')
    ->all();
```

### Model Form vs Active Record

- **Active Record Models**: Represent database tables, extend `yii\db\ActiveRecord`
- **Form Models**: Handle input validation only, extend `yii\base\Model`

```php
// Form model
class LoginForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute)
    {
        // Custom validation logic
    }
}
```

---

## Views

### View Files

View files are PHP templates with access to passed variables:

```php
<!-- views/site/index.php -->
<?php
use yii\helpers\Html;

$this->title = 'Welcome';
$this->params['breadcrumbs'] = [['label' => 'Home']];
?>

<div class="site-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p><?= Html::encode($message) ?></p>
</div>
```

### Layouts

Layouts wrap content views:

```php
<!-- views/layouts/main.php -->
<?php
use yii\web\View;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= Html::encode($this->title) ?></title>
</head>
<body>
    <header><!-- Header --></header>

    <main>
        <?= $content ?>  <!-- Content view renders here -->
    </main>

    <footer><!-- Footer --></footer>
</body>
</html>
```

### Rendering Views

```php
// Render view in layout (from controller)
return $this->render('view', ['data' => $data]);

// Render view without layout
return $this->renderContent('view', ['data' => $data]);

// Render partial without layout (from anywhere)
$html = $this->renderPartial('_partial', ['data' => $data]);
```

### Widgets

Widgets are reusable UI components:

```php
<!-- Using a widget -->
<?= yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_item',  // Render _item.php for each item
    'emptyText' => 'No results',
]) ?>

<!-- Form widget -->
<?php $form = yii\widgets\ActiveForm::begin(['id' => 'form']); ?>
    <?= $form->field($model, 'username') ?>
    <?= $form->field($model, 'email') ?>
    <button type="submit">Submit</button>
<?php yii\widgets\ActiveForm::end(); ?>
```

### Assets (CSS/JS)

Asset bundles define CSS/JS dependencies:

```php
namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $basePath = '@web';

    public $css = [
        'css/site.css',
    ];

    public $js = [
        'js/site.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}

// Register in view
<?php
app\assets\AppAsset::register($this);
?>
```

---

## Components

### Application Components

Components provide services to the application:

```php
// Register in config/web.php
'components' => [
    'db' => [
        'class' => 'yii\db\Connection',
        'dsn' => 'mysql:host=localhost;dbname=mydb',
        'username' => 'root',
        'password' => '',
    ],
    'cache' => [
        'class' => 'yii\caching\FileCache',
    ],
    'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
        'viewPath' => '@app/mail',
    ],
],

// Access component
$db = Yii::$app->db;
$cache = Yii::$app->cache;
$mailer = Yii::$app->mailer;
```

### Creating Custom Components

```php
namespace app\components;

use yii\base\Component;

class MyService extends Component
{
    public $config = 'default';

    public function init()
    {
        parent::init();
        // Initialize component
    }

    public function doSomething()
    {
        return "Doing something with {$this->config}";
    }
}

// Register in config
'components' => [
    'myService' => [
        'class' => 'app\components\MyService',
        'config' => 'custom',
    ],
],

// Use
Yii::$app->myService->doSomething();
```

### Dependency Injection Container

Yii has a built-in DI container:

```php
// Register a singleton
Yii::$container->setSingleton('myService', ['class' => 'app\components\MyService']);

// Register a factory
Yii::$container->set('myService', ['class' => 'app\components\MyService']);

// Automatic injection
class MyController extends Controller
{
    public function __construct(MyService $service)
    {
        $this->service = $service;
    }
}
```

---

## Security

### CSRF Protection

CSRF tokens prevent cross-site request forgery:

```php
// ENABLED BY DEFAULT for POST/PUT/DELETE requests

// In view/form (automatic with ActiveForm)
<?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'field') ?>
<?php ActiveForm::end(); ?>

// Disable in controller if needed (for APIs)
public $enableCsrfValidation = false;

// Manually validate
if (!Yii::$app->request->validateCsrfToken()) {
    throw new BadRequestHttpException('Invalid CSRF token');
}
```

### Input Validation and Sanitization

ALWAYS validate user input:

```php
// In model rules - validation ensures safety
public function rules()
{
    return [
        ['username', 'required'],
        ['username', 'string', 'max' => 255],
        ['email', 'email'],
    ];
}

// Use scenarios to control what can be set
$model = new User();
$model->scenario = 'create';
$model->load(Yii::$app->request->post());  // Only loads safe attributes
```

### SQL Injection Prevention

Always use parameter binding:

```php
// SAFE - Parameter binding
$users = User::find()
    ->where(['username' => $username])
    ->all();

// SAFE - Query builder
$users = User::find()
    ->where(['=', 'username', $username])
    ->all();

// UNSAFE - Never do this!
$users = User::find()
    ->where("username = '$username'")  // VULNERABLE!
    ->all();
```

### XSS Prevention

Always encode output:

```php
<!-- SAFE - Auto-encoded -->
<?= $userInput ?>

<!-- UNSAFE - Raw HTML (only for trusted content!) -->
<?= $userInput ?>  <!-- Use only if sanitized -->

<!-- Safe encoding -->
<?= Html::encode($userInput) ?>
<?= Html::tag('div', $userInput) ?>

<!-- For rich text, use HtmlPurifier -->
<?= HtmlPurifier::process($userInput) ?>
```

### Authentication

```php
// Login
$user = User::findOne(['username' => $username]);
if ($user && $user->validatePassword($password)) {
    Yii::$app->user->login($user);
}

// Check authentication
if (Yii::$app->user->isGuest) {
    // User not authenticated
}

// Get authenticated user
$currentUser = Yii::$app->user->identity;

// Logout
Yii::$app->user->logout();
```

### Authorization (RBAC)

```php
// Check permission
if (Yii::$app->user->can('edit-post')) {
    // User has permission
}

// Assign role to user
Yii::$app->authManager->assign(
    Yii::$app->authManager->getRole('admin'),
    $userId
);
```

---

## Performance

### Database Optimization

```php
// LAZY LOADING - N+1 Problem! (BAD)
$users = User::find()->all();
foreach ($users as $user) {
    echo $user->profile->name;  // Additional query for each user!
}

// EAGER LOADING - Recommended!
$users = User::find()
    ->with('profile')  // Load profile in one query
    ->all();

// LIMIT OUTPUT - Don't select all columns
$users = User::find()
    ->select(['id', 'username'])  // Only needed columns
    ->asArray()                    // Skip object hydration
    ->limit(10)
    ->all();

// Use Pagination
$query = User::find();
$pagination = new Pagination([
    'totalCount' => $query->count(),
    'pageSize' => 20,
]);
$users = $query
    ->offset($pagination->offset)
    ->limit($pagination->limit)
    ->all();
```

### Caching

```php
// Data caching
$data = Yii::$app->cache->get('key');
if ($data === false) {
    $data = expensiveOperation();
    Yii::$app->cache->set('key', $data, 3600);  // Cache for 1 hour
}

// Query caching
$users = User::find()
    ->cache(3600)  // Cache result for 1 hour
    ->all();

// Fragment caching (in views)
<?php if ($this->beginCache('unique-id', ['duration' => 3600])) { ?>
    <!-- Cached content -->
<?php $this->endCache(); } ?>
```

### HTTP Caching

```php
// In controller
public function actionView($id)
{
    $post = Post::findOne($id);

    // Set cache headers
    Yii::$app->response->headers['Cache-Control'] = 'max-age=3600, public';
    Yii::$app->response->headers['ETag'] = md5(json_encode($post));

    return $this->render('view', ['post' => $post]);
}
```

---

## Console Commands

### Command Basics

Console commands extend `yii\console\Controller`:

```php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

class ExampleController extends Controller
{
    /**
     * This is the default action
     * php yii example
     */
    public function actionIndex()
    {
        $this->stdout("Hello World!\n");
        return ExitCode::OK;
    }

    /**
     * This action has parameter
     * php yii example/test arg1
     */
    public function actionTest($arg)
    {
        $this->stdout("Argument: $arg\n");
        return ExitCode::OK;
    }
}
```

### Console I/O

```php
// Output
$this->stdout("Normal text\n");
$this->stdout("Success\n", 32);      // Green
$this->stdout("Warning\n", 33);      // Yellow
$this->stdout("Error\n", 31);        // Red

// Input
$answer = $this->prompt('Your name?');
$confirm = $this->confirm('Are you sure?');
$select = $this->select('Choose option', ['a' => 'Option A', 'b' => 'Option B']);
```

---

## Common Patterns

### Module Structure

```php
// modules/api/Module.php
namespace app\modules\api;

class Module extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\api\controllers';

    public function init()
    {
        parent::init();
        // Add URL rules
        \Yii::$app->urlManager->addRules([
            'api/users' => 'api/user/index',
            'api/users/<id>' => 'api/user/view',
        ]);
    }
}

// Register in config
'modules' => [
    'api' => [
        'class' => 'app\modules\api\Module',
    ],
],
```

### RESTful API

```php
use yii\rest\Controller;

class UserController extends Controller
{
    public function actions()
    {
        return [
            'index' => [
                'class' => 'yii\rest\IndexAction',
                'modelClass' => 'app\models\User',
            ],
            'view' => [
                'class' => 'yii\rest\ViewAction',
                'modelClass' => 'app\models\User',
            ],
            'create' => [
                'class' => 'yii\rest\CreateAction',
                'modelClass' => 'app\models\User',
            ],
            'update' => [
                'class' => 'yii\rest\UpdateAction',
                'modelClass' => 'app\models\User',
            ],
            'delete' => [
                'class' => 'yii\rest\DeleteAction',
                'modelClass' => 'app\models\User',
            ],
        ];
    }
}
```

### Migrations

```php
namespace app\migrations;

use yii\db\Migration;

class m210101_000000_create_user_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->unique(),
            'email' => $this->string(255)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}

// Run: php yii migrate
```

---

## Anti-Patterns

### ❌ Don't: Echo in Controllers

```php
// WRONG
public function actionIndex()
{
    echo "Hello";  // Bad! Hard to test and control
}

// CORRECT
public function actionIndex()
{
    return $this->render('index');  // Proper return
}
```

### ❌ Don't: Business Logic in Views

```php
// WRONG - View with logic
<?php foreach($posts as $post): ?>
    <?php if($post->author_id == Yii::$app->user->id): ?>
        <?= $post->title ?>
    <?php endif; ?>
<?php endforeach; ?>

// CORRECT - Pre-filter in controller
$posts = Post::find()->where(['author_id' => Yii::$app->user->id])->all();
return $this->render('index', ['posts' => $posts]);
```

### ❌ Don't: Fat Controllers

```php
// WRONG - Too much logic in controller
public function actionStore()
{
    $model = new Post();
    if ($model->load(Yii::$app->request->post())) {
        // Validate
        // Process
        // Send email
        // Update cache
        // All here!
    }
}

// CORRECT - Extract to service
public function actionStore()
{
    $model = new Post();
    if ($model->load(Yii::$app->request->post())) {
        Yii::$app->postService->store($model);
    }
}
```

### ❌ Don't: Ignore Validation

```php
// WRONG
$user = new User();
$user->username = $_POST['username'];
$user->save();  // No validation!

// CORRECT
$user = new User();
$user->load(Yii::$app->request->post());
if ($user->validate() && $user->save()) {
    // Success
}
```

### ❌ Don't: Hardcode Values

```php
// WRONG
$email = 'admin@example.com';  // Hardcoded

// CORRECT
$email = Yii::$app->params['adminEmail'];
```

### ❌ Don't: Direct Database Access Outside Models

```php
// WRONG
$result = Yii::$app->db->createCommand('SELECT * FROM user WHERE id=' . $id)->queryOne();

// CORRECT
$user = User::findOne($id);
```

---

## Summary

Key principles for Yii2 development:

1. **Always validate input** - Use model rules
2. **Use eager loading** - Avoid N+1 queries
3. **Separate concerns** - Controllers, Models, Views
4. **Leverage built-in features** - Security, caching, validation
5. **Write testable code** - Inject dependencies
6. **Use configurations** - Don't hardcode values
7. **Follow naming conventions** - Makes code discoverable
8. **Cache strategically** - Database and HTTP caching
9. **Secure by default** - CSRF, XSS, SQL injection prevention
10. **Keep it DRY** - Don't repeat yourself
