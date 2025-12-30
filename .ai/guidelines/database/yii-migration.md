```php
<?php

/**
 * AI Guideline: Yii 2.0 Database Migration Structure
 *
 * This file serves as a reference for creating Database Migrations in Yii 2.
 * Migrations facilitate database schema versioning and modification.
 *
 * @see https://www.yiiframework.com/doc/api/2.0/yii-db-migration
 */

namespace yii\db;

use yii\base\Component;

/**
 * Migration is the base class for representing a database migration.
 *
 * It provides methods for creating, altering, and dropping tables and columns.
 *
 * Common Methods:
 * - `up()`: Contains the logic to apply the migration (e.g., create table).
 * - `down()`: Contains the logic to revert the migration (e.g., drop table).
 * - `safeUp()`: Transactional version of up().
 * - `safeDown()`: Transactional version of down().
 */
class Migration extends Component
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     */
    public $db = 'db';

    /**
     * @var bool whether to use transactions for safeUp() and safeDown()
     */
    public $useTransaction = true;

    /**
     * Creates a new table.
     *
     * Example:
     * ```php
     * $this->createTable('{{%post}}', [
     *     'id' => $this->primaryKey(),
     *     'title' => $this->string()->notNull(),
     *     'body' => $this->text(),
     *     'created_at' => $this->integer(),
     * ]);
     * ```
     *
     * @param string $table the name of the table to be created.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string $options additional SQL fragment (e.g. engine type) for the create table.
     */
    public function createTable($table, $columns, $options = null)
    {
    }

    /**
     * Drops a table.
     *
     * @param string $table the name of the table to be dropped.
     */
    public function dropTable($table)
    {
    }

    /**
     * Adds a column to a table.
     *
     * @param string $table the table that the new column will be added to.
     * @param string $column the name of the new column.
     * @param string $type the column type (e.g. 'string', 'integer').
     */
    public function addColumn($table, $column, $type)
    {
    }

    /**
     * Drops a column from a table.
     *
     * @param string $table the table where the column is.
     * @param string $column the name of the column to be dropped.
     */
    public function dropColumn($table, $column)
    {
    }

    /**
     * Creates a primary key.
     * @return \yii\db\ColumnSchemaBuilder
     */
    public function primaryKey($length = null)
    {
        return new ColumnSchemaBuilder('pk', $length);
    }

    /**
     * Creates a string column.
     * @return \yii\db\ColumnSchemaBuilder
     */
    public function string($length = null)
    {
        return new ColumnSchemaBuilder('string', $length);
    }

    /**
     * Creates an integer column.
     * @return \yii\db\ColumnSchemaBuilder
     */
    public function integer($length = null)
    {
        return new ColumnSchemaBuilder('integer', $length);
    }
    
    /**
     * Creates a foreign key constraint.
     *
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option.
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
    }

    /**
     * Creates an index on the table.
     * 
     * @param string $name the name of the index.
     * @param string $table the table that the index will be added to.
     * @param string|array $columns the name of the column(s) that the index will be added on.
     * @param bool $unique whether to add a UNIQUE index.
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
    }
}
\n```
