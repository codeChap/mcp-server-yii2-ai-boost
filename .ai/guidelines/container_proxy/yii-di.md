# Yii2 Dependency Injection

## DI Container
```php
// Register class
Yii::$container->set('app\components\MyService', [
    'class' => 'app\components\MyService',
    'property' => 'value',
]);

// Register singleton
Yii::$container->setSingleton('app\components\MyService', [...]);

// Get instance
$service = Yii::$container->get('app\components\MyService');

// Constructor injection (automatic)
class MyController extends Controller
{
    public function __construct(MyService $service, $id, $module, $config = [])
    {
        $this->service = $service;
        parent::__construct($id, $module, $config);
    }
}
```

## Service Locator (Yii::$app)
```php
// Configure in config/web.php
'components' => [
    'myService' => [
        'class' => 'app\components\MyService',
        'property' => 'value',
    ],
],

// Access component
Yii::$app->myService->doSomething();
Yii::$app->get('myService');
Yii::$app->has('myService'); // Check if registered
```

## Interface Binding
```php
Yii::$container->set('app\interfaces\PaymentInterface', 'app\components\StripePayment');

// Now any class depending on PaymentInterface gets StripePayment
class OrderService
{
    public function __construct(PaymentInterface $payment)
    {
        $this->payment = $payment;
    }
}
```
