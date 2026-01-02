<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use Yii;
use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

/**
 * Database Query Tool
 *
 * Execute SQL queries against the database and return results.
 * Intended for development use - allows AI assistants to explore
 * and query data during debugging and development.
 */
class DatabaseQueryTool extends BaseTool
{
    /**
     * Default maximum rows to return
     */
    private const DEFAULT_LIMIT = 100;

    /**
     * Absolute maximum rows allowed
     */
    private const MAX_LIMIT = 1000;

    public function getName(): string
    {
        return 'database_query';
    }

    public function getDescription(): string
    {
        return 'Execute SQL queries against the database and return results';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'sql' => [
                    'type' => 'string',
                    'description' => 'SQL query to execute',
                ],
                'params' => [
                    'type' => 'object',
                    'description' => 'Bound parameters for the query (e.g., {":id": 1})',
                    'additionalProperties' => true,
                ],
                'db' => [
                    'type' => 'string',
                    'description' => 'Database connection name (default: db)',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum rows to return (default: 100, max: 1000)',
                ],
            ],
            'required' => ['sql'],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $sql = $arguments['sql'] ?? '';
        $params = $arguments['params'] ?? [];
        $dbName = $arguments['db'] ?? 'db';
        $limit = $arguments['limit'] ?? self::DEFAULT_LIMIT;

        // Validate SQL is not empty
        if (empty(trim($sql))) {
            throw new \Exception('SQL query cannot be empty');
        }

        // Cap the limit
        $limit = min(max(1, (int) $limit), self::MAX_LIMIT);

        // Get database connection
        if (!Yii::$app->has($dbName)) {
            throw new \Exception("Database connection '$dbName' not found");
        }

        $db = Yii::$app->get($dbName);

        // Add LIMIT if not present and query appears to be a SELECT
        $sql = $this->ensureLimit($sql, $limit);

        try {
            $command = $db->createCommand($sql);

            // Bind parameters if provided
            if (!empty($params)) {
                foreach ($params as $name => $value) {
                    $command->bindValue($name, $value);
                }
            }

            // Execute and get results
            $startTime = microtime(true);
            $rows = $command->queryAll();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $result = [
                'success' => true,
                'row_count' => count($rows),
                'duration_ms' => $duration,
                'rows' => $this->sanitize($rows),
            ];

            // Warn if results were likely truncated
            if (count($rows) === $limit) {
                $result['warning'] = "Results may be truncated at $limit rows. Use 'limit' parameter to increase.";
            }

            return $result;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'sql' => $sql,
            ];
        }
    }

    /**
     * Ensure SELECT queries have a LIMIT clause
     *
     * @param string $sql SQL query
     * @param int $limit Row limit
     * @return string Modified SQL
     */
    private function ensureLimit(string $sql, int $limit): string
    {
        $trimmedSql = trim($sql);

        // Only add LIMIT to SELECT statements that don't already have one
        if (
            preg_match('/^\s*SELECT\s/i', $trimmedSql) &&
            !preg_match('/\sLIMIT\s+\d+/i', $trimmedSql)
        ) {
            // Remove trailing semicolon if present
            $trimmedSql = rtrim($trimmedSql, ';');
            return $trimmedSql . ' LIMIT ' . $limit;
        }

        return $trimmedSql;
    }
}
