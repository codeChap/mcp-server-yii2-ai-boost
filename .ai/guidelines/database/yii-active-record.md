# Yii2 Active Record

```php
namespace yii\db;

class ActiveRecord extends Model
{
    public static function tableName()
    {
        return '{{%table_name}}';
    }

    public static function primaryKey()
    {
        return ['id'];
    }

    public function rules()
    {
        return [
            [['field1', 'field2'], 'required'],
            ['email', 'email'],
            ['status', 'integer'],
            ['title', 'string', 'max' => 255],
            ['field', 'unique'],
            ['field', 'in', 'range' => [1, 2, 3]],
        ];
    }

    // Relations
    public function getRelatedOne()
    {
        return $this->hasOne(Related::class, ['id' => 'related_id']);
    }

    public function getRelatedMany()
    {
        return $this->hasMany(Related::class, ['parent_id' => 'id']);
    }

    // CRUD
    public static function find();                    // Returns ActiveQuery
    public static function findOne($condition);       // Single record or null
    public static function findAll($condition);       // Array of records
    public function save($runValidation = true);      // Insert or update
    public function delete();                         // Delete record
    public function validate($attributeNames = null); // Validate attributes
}
```

## Query Examples
```php
// Find
$user = User::findOne(1);
$user = User::findOne(['email' => 'test@example.com']);
$users = User::find()->where(['status' => 1])->all();

// Eager loading (avoid N+1)
$users = User::find()->with('profile', 'posts')->all();

// Query builder
User::find()
    ->select(['id', 'username'])
    ->where(['status' => 1])
    ->andWhere(['>', 'created_at', $date])
    ->orderBy(['created_at' => SORT_DESC])
    ->limit(10)
    ->asArray()
    ->all();

// Save
$user = new User();
$user->username = 'john';
$user->save();

// Update
$user->updateAttributes(['status' => 2]);
User::updateAll(['status' => 0], ['<', 'last_login', $expiry]);

// Delete
$user->delete();
User::deleteAll(['status' => 0]);
```
