# Yii2 Events

## Instance-Level Events
```php
// Attach handler
$model->on(ActiveRecord::EVENT_BEFORE_INSERT, function($event) {
    $event->sender->created_at = time();
});

// Detach handler
$model->off(ActiveRecord::EVENT_BEFORE_INSERT, $handler);

// Trigger custom event
$model->trigger('myEvent', new Event(['data' => $value]));
```

## Class-Level Events
```php
// Attach to all instances of a class
Event::on(Post::class, ActiveRecord::EVENT_AFTER_INSERT, function($event) {
    // Runs for all Post inserts
});

// Detach
Event::off(Post::class, ActiveRecord::EVENT_AFTER_INSERT);
```

## Common ActiveRecord Events
```php
ActiveRecord::EVENT_BEFORE_INSERT
ActiveRecord::EVENT_AFTER_INSERT
ActiveRecord::EVENT_BEFORE_UPDATE
ActiveRecord::EVENT_AFTER_UPDATE
ActiveRecord::EVENT_BEFORE_DELETE
ActiveRecord::EVENT_AFTER_DELETE
ActiveRecord::EVENT_AFTER_FIND
```

## Custom Events in Components
```php
class MyComponent extends Component
{
    const EVENT_SOMETHING_HAPPENED = 'somethingHappened';

    public function doSomething()
    {
        // ... logic ...
        $this->trigger(self::EVENT_SOMETHING_HAPPENED, new Event([
            'data' => $result,
        ]));
    }
}

// Usage
$component->on(MyComponent::EVENT_SOMETHING_HAPPENED, function($event) {
    Yii::info('Something happened: ' . $event->data);
});
```

## Stop Propagation
```php
$model->on('myEvent', function($event) {
    $event->handled = true; // Stops further handlers
});
```
