# Yii2 Authentication & RBAC

## User Component
```php
// Check auth status
Yii::$app->user->isGuest;      // true if not logged in
Yii::$app->user->identity;     // Current user model or null
Yii::$app->user->id;           // Current user ID

// Login/Logout
Yii::$app->user->login($identity, $duration = 0);
Yii::$app->user->logout($destroySession = true);

// Permission check
Yii::$app->user->can('updatePost', ['post' => $post]);
```

## Identity Interface
```php
class User extends ActiveRecord implements IdentityInterface
{
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
}
```

## RBAC Manager
```php
$auth = Yii::$app->authManager;

// Create permission
$createPost = $auth->createPermission('createPost');
$createPost->description = 'Create a post';
$auth->add($createPost);

// Create role
$author = $auth->createRole('author');
$auth->add($author);
$auth->addChild($author, $createPost);

// Assign role to user
$auth->assign($author, $userId);

// Check permission
$auth->checkAccess($userId, 'createPost');
```

## Access Control Filter
```php
public function behaviors()
{
    return [
        'access' => [
            'class' => AccessControl::class,
            'rules' => [
                ['actions' => ['login'], 'allow' => true, 'roles' => ['?']], // Guests
                ['actions' => ['logout'], 'allow' => true, 'roles' => ['@']], // Authenticated
                ['allow' => true, 'roles' => ['admin']],
            ],
        ],
    ];
}
```
