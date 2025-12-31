# Yii2 Web Controller

```php
namespace yii\web;

class Controller extends \yii\base\Controller
{
    /** @var bool enable CSRF validation (default: true) */
    public $enableCsrfValidation = true;

    // Rendering
    public function render($view, $params = []);      // With layout
    public function renderPartial($view, $params = []); // Without layout
    public function renderAjax($view, $params = []);  // For AJAX (injects JS/CSS)

    // Redirects
    public function redirect($url, $statusCode = 302);
    public function goBack($defaultUrl = null);
    public function goHome();
    public function refresh();

    // External actions
    public function actions()
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
            'captcha' => ['class' => 'yii\captcha\CaptchaAction'],
        ];
    }
}
```

## Usage Example
```php
namespace app\controllers;

use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['login', 'error'], 'allow' => true],
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
        return $this->render('index', ['data' => $data]);
    }

    public function actionCreate()
    {
        $model = new Post();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }
}
```
