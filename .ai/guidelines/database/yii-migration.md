# Yii2 Database Migration

```php
namespace yii\db;

class Migration extends Component
{
    public $db = 'db';

    // Table operations
    public function createTable($table, $columns, $options = null);
    public function dropTable($table);
    public function renameTable($table, $newName);
    public function truncateTable($table);

    // Column operations
    public function addColumn($table, $column, $type);
    public function dropColumn($table, $column);
    public function renameColumn($table, $name, $newName);
    public function alterColumn($table, $column, $type);

    // Index operations
    public function createIndex($name, $table, $columns, $unique = false);
    public function dropIndex($name, $table);

    // Foreign key operations
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null);
    public function dropForeignKey($name, $table);

    // Column types
    public function primaryKey($length = null);
    public function bigPrimaryKey($length = null);
    public function string($length = null);
    public function text();
    public function integer($length = null);
    public function bigInteger($length = null);
    public function boolean();
    public function float($precision = null);
    public function decimal($precision = null, $scale = null);
    public function dateTime($precision = null);
    public function timestamp($precision = null);
    public function date();
    public function binary($length = null);
    public function json();
}
```

## Usage Example
```php
class m210101_000000_create_user extends Migration
{
    public function up()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(255)->notNull()->unique(),
            'email' => $this->string(255)->notNull(),
            'status' => $this->integer()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createIndex('idx-user-email', '{{%user}}', 'email', true);
    }

    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}
```

Commands: `php yii migrate`, `php yii migrate/down`, `php yii migrate/create name`
