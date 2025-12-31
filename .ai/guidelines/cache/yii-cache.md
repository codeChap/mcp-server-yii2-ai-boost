# Yii2 Caching

```php
namespace yii\caching;

abstract class Cache extends Component
{
    public function get($key);                                    // Get cached value or false
    public function set($key, $value, $duration = 0, $dep = null); // Store value
    public function add($key, $value, $duration = 0, $dep = null); // Store if not exists
    public function delete($key);                                  // Delete value
    public function flush();                                       // Clear all cache
    public function exists($key);                                  // Check if key exists
    
    // Recommended: get or compute
    public function getOrSet($key, $callable, $duration = null, $dependency = null);
}
```

## Backends
- `FileCache` - File-based (default)
- `MemCache` - Memcached
- `RedisCache` - Redis (via yii2-redis)
- `DbCache` - Database
- `DummyCache` - No-op for testing

## Configuration
```php
'components' => [
    'cache' => [
        'class' => 'yii\caching\FileCache',
        'cachePath' => '@runtime/cache',
    ],
],
```

## Usage
```php
// Simple get/set
$data = Yii::$app->cache->get('key');
if ($data === false) {
    $data = expensiveOperation();
    Yii::$app->cache->set('key', $data, 3600);
}

// Preferred: getOrSet
$data = Yii::$app->cache->getOrSet('key', function() {
    return expensiveOperation();
}, 3600);

// Query caching
$users = User::find()->cache(3600)->all();

// With dependency
$dep = new DbDependency(['sql' => 'SELECT MAX(updated_at) FROM post']);
Yii::$app->cache->set('posts', $posts, 3600, $dep);
```
