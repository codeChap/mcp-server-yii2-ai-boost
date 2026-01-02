<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use Yii;
use yii\db\TableSchema;
use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

/**
 * Database Schema Tool
 *
 * Provides complete database introspection including:
 * - Database connections
 * - Tables with row counts
 * - Table schemas (columns, types, defaults)
 * - Indexes and constraints
 * - Foreign key relationships
 * - Active Record model discovery
 */
final class DatabaseSchemaTool extends BaseTool
{
    public function getName(): string
    {
        return 'database_schema';
    }

    public function getDescription(): string
    {
        return 'Inspect database schema including tables, columns, indexes, and Active Record models';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'db' => [
                    'type' => 'string',
                    'description' => 'Database connection name (default: main)',
                ],
                'table' => [
                    'type' => 'string',
                    'description' => 'Specific table to inspect (optional)',
                ],
                'include' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'What to include: tables, schema, indexes, models',
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $dbName = $arguments['db'] ?? 'db';
        $table = $arguments['table'] ?? null;
        $include = $arguments['include'] ?? ['tables', 'schema'];

        // Expand 'all' to include all available options
        if (in_array('all', $include)) {
            $include = ['tables', 'schema', 'indexes', 'models'];
        }

        if (!Yii::$app->has($dbName)) {
            throw new \Exception("Database connection '$dbName' not found");
        }

        $db = Yii::$app->get($dbName);
        $result = [];

        if (in_array('tables', $include)) {
            $result['tables'] = $this->getTables($db, $table);
        }

        if (in_array('schema', $include) && $table) {
            $result['schema'] = $this->getTableSchema($db, $table);
        }

        if (in_array('indexes', $include)) {
            if ($table) {
                $result['indexes'] = $this->getTableIndexes($db, $table);
            } else {
                $result['indexes'] = 'Please specify a table name';
            }
        }

        if (in_array('models', $include)) {
            $result['models'] = $this->getActiveRecordModels();
        }

        return $result;
    }

    /**
     * Get list of tables with row counts
     *
     * @param object $db Database connection
     * @param string|null $table Specific table
     * @return array
     */
    private function getTables(object $db, ?string $table = null): array
    {
        $schema = $db->getSchema();
        $tables = [];

        $tableNames = $table ? [$table] : $schema->getTableNames();

        foreach ($tableNames as $tableName) {
            try {
                $rowCount = (int) $db->createCommand(
                    "SELECT COUNT(*) FROM [[" . $tableName . "]]"
                )->queryScalar();

                $tables[$tableName] = [
                    'name' => $tableName,
                    'row_count' => $rowCount,
                ];
            } catch (\Exception $e) {
                $tables[$tableName] = [
                    'name' => $tableName,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $tables;
    }

    /**
     * Get detailed schema for a table
     *
     * @param object $db Database connection
     * @param string $table Table name
     * @return array
     */
    private function getTableSchema(object $db, string $table): array
    {
        $schema = $db->getSchema();
        $tableSchema = $schema->getTableSchema($table);

        if (!$tableSchema) {
            throw new \Exception("Table '$table' not found");
        }

        $columns = [];
        foreach ($tableSchema->columns as $name => $column) {
            $columns[$name] = [
                'name' => $name,
                'type' => $column->type,
                'db_type' => $column->dbType,
                'php_type' => $column->phpType,
                'size' => $column->size,
                'precision' => $column->precision,
                'scale' => $column->scale,
                'not_null' => $column->allowNull ? false : true,
                'default' => $column->defaultValue,
                'autoIncrement' => $column->autoIncrement ? true : false,
                'comment' => $column->comment,
            ];
        }

        $result = [
            'table' => $table,
            'columns' => $columns,
            'primary_key' => $tableSchema->primaryKey,
        ];

        // Add foreign keys if supported
        if (method_exists($schema, 'getTableForeignKeys')) {
            try {
                $fks = $schema->getTableForeignKeys($table);
                if ($fks) {
                    $result['foreign_keys'] = $fks;
                }
            } catch (\Exception $e) {
                // Foreign keys not supported on this database
            }
        }

        return $result;
    }

    /**
     * Get indexes for a table
     *
     * @param object $db Database connection
     * @param string $table Table name
     * @return array
     */
    private function getTableIndexes(object $db, string $table): array
    {
        $schema = $db->getSchema();

        try {
            $indexes = $schema->getTableIndexes($table);
            return [
                'table' => $table,
                'indexes' => $indexes,
            ];
        } catch (\Exception $e) {
            return [
                'table' => $table,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Discover Active Record models in models directory
     *
     * @return array
     */
    private function getActiveRecordModels(): array
    {
        $modelsPath = Yii::getAlias('@app/models');
        if (!is_dir($modelsPath)) {
            return [];
        }

        $models = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($modelsPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $this->getClassNameFromFile($file->getPathname());
                if ($className && $this->isActiveRecordModel($className)) {
                    $models[] = $className;
                }
            }
        }

        return $models;
    }

    /**
     * Extract class name from PHP file
     *
     * @param string $file File path
     * @return string|null
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $namespace = '';
        $className = '';

        $tokens = token_get_all(file_get_contents($file));

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= $tokens[$j][1];
                    } elseif ($tokens[$j][0] === T_NS_SEPARATOR) {
                        $namespace .= '\\';
                    } elseif ($tokens[$j][0] === ';') {
                        break;
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $className = $tokens[$j][1];
                        break;
                    }
                }
            }
        }

        return $namespace && $className ? $namespace . '\\' . $className : null;
    }

    /**
     * Check if a class is an Active Record model
     *
     * @param string $className Class name
     * @return bool
     */
    private function isActiveRecordModel(string $className): bool
    {
        try {
            if (!class_exists($className)) {
                return false;
            }

            $reflection = new \ReflectionClass($className);
            $parent = $reflection->getParentClass();

            while ($parent) {
                if ($parent->getName() === 'yii\db\ActiveRecord') {
                    return true;
                }
                $parent = $parent->getParentClass();
            }

            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
