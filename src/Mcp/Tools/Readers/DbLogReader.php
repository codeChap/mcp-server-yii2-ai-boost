<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Readers;

use Yii;
use yii\log\DbTarget;
use yii\log\Logger;

/**
 * Database log reader
 *
 * Reads logs from DbTarget (database log table)
 */
final class DbLogReader implements LogReaderInterface
{
    /**
     * @var DbTarget|object|null The DbTarget instance (or compatible custom implementation)
     *
     * NOTE: We use untyped property here to support custom DbTarget implementations
     * that may not extend yii\log\DbTarget but have the same interface (logTable, db).
     * Example: app\components\log\DbTarget
     */
    private $target = null;

    /**
     * Level name to constant mapping
     *
     * @var array
     */
    private const LEVEL_MAP = [
        'error' => Logger::LEVEL_ERROR,
        'warning' => Logger::LEVEL_WARNING,
        'info' => Logger::LEVEL_INFO,
        'trace' => Logger::LEVEL_TRACE,
        'profile' => Logger::LEVEL_PROFILE,
    ];

    /**
     * Reverse mapping for display
     *
     * @var array
     */
    private const LEVEL_NAMES = [
        Logger::LEVEL_ERROR => 'error',
        Logger::LEVEL_WARNING => 'warning',
        Logger::LEVEL_INFO => 'info',
        Logger::LEVEL_TRACE => 'trace',
        Logger::LEVEL_PROFILE => 'profile',
    ];

    public function __construct()
    {
        $this->findDbTarget();
    }

    public function isAvailable(): bool
    {
        return $this->target !== null;
    }

    public function getSource(): string
    {
        return 'db';
    }

    public function read(array $params): array
    {
        if (!$this->isAvailable()) {
            return [
                'logs' => [],
                'summary' => [
                    'total_available' => 0,
                    'returned' => 0,
                    'sources' => ['db' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'DbTarget not configured',
            ];
        }

        try {
            // Parse parameters
            $levels = $this->parseLevels($params['levels'] ?? ['error', 'warning']);
            $categories = $params['categories'] ?? ['*'];
            $limit = (int) ($params['limit'] ?? 100);
            $offset = (int) ($params['offset'] ?? 0);
            $search = $params['search'] ?? null;
            $timeRange = $params['time_range'] ?? null;
            $includeTrace = (bool) ($params['include_trace'] ?? false);

            // Build query
            /** @var object $target */
            $target = $this->target;
            $db = $target->db;
            $table = $target->logTable;

            // Count total matching records
            $countQuery = $db->createCommand(
                "SELECT COUNT(*) FROM [[$table]] WHERE [[level]] IN (" . implode(',', $levels) . ")"
            );

            // Build WHERE clause
            $whereConditions = ["[[level]] IN (" . implode(',', $levels) . ")"];
            $params_sql = [];

            // Add category filter
            if ($categories !== ['*']) {
                $categoryConditions = [];
                foreach ($categories as $category) {
                    if (str_ends_with($category, '*')) {
                        $prefix = rtrim($category, '*');
                        $categoryConditions[] = "[[category]] LIKE :cat_" . count($categoryConditions);
                        $params_sql[':cat_' . (count($categoryConditions) - 1)] = $prefix . '%';
                    } else {
                        $categoryConditions[] = "[[category]] = :cat_" . count($categoryConditions);
                        $params_sql[':cat_' . (count($categoryConditions) - 1)] = $category;
                    }
                }
                if (!empty($categoryConditions)) {
                    $whereConditions[] = '(' . implode(' OR ', $categoryConditions) . ')';
                }
            }

            // Add search filter
            if ($search !== null) {
                $whereConditions[] = "[[message]] LIKE :search";
                $params_sql[':search'] = '%' . $search . '%';
            }

            // Add time range filter
            if ($timeRange !== null) {
                if (isset($timeRange['start'])) {
                    $whereConditions[] = "[[log_time]] >= :time_start";
                    $params_sql[':time_start'] = (int) $timeRange['start'];
                }
                if (isset($timeRange['end'])) {
                    $whereConditions[] = "[[log_time]] <= :time_end";
                    $params_sql[':time_end'] = (int) $timeRange['end'];
                }
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Get total count
            $countQuery = $db->createCommand(
                "SELECT COUNT(*) FROM [[$table]] WHERE $whereClause",
                $params_sql
            );
            $total = (int) $countQuery->queryScalar();

            // Fetch logs
            $sql = "SELECT * FROM [[$table]] WHERE $whereClause ORDER BY [[log_time]] DESC LIMIT $limit OFFSET $offset";
            $rows = $db->createCommand($sql, $params_sql)->queryAll();

            // Format output
            $logs = [];
            $earliestTime = null;
            $latestTime = null;
            $levelsFound = [];

            foreach ($rows as $row) {
                $log = $this->formatRow($row, $includeTrace);
                $logs[] = $log;

                // Track time range and levels
                if ($latestTime === null || $log['timestamp'] > $latestTime) {
                    $latestTime = $log['timestamp'];
                }
                if ($earliestTime === null || $log['timestamp'] < $earliestTime) {
                    $earliestTime = $log['timestamp'];
                }
                $levelsFound[$log['level']] = true;
            }

            return [
                'logs' => $logs,
                'summary' => [
                    'total_available' => $total,
                    'returned' => count($logs),
                    'sources' => ['db' => count($logs)],
                    'levels_found' => array_keys($levelsFound),
                    'time_range' => [
                        'earliest' => $earliestTime,
                        'latest' => $latestTime,
                    ],
                ],
                'source' => $this->getSource(),
            ];
        } catch (\Exception $e) {
            return [
                'logs' => [],
                'summary' => [
                    'total_available' => 0,
                    'returned' => 0,
                    'sources' => ['db' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'Failed to query logs: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Find DbTarget in configured targets
     *
     * @return void
     */
    private function findDbTarget(): void
    {
        if (!Yii::$app->has('log')) {
            return;
        }

        $dispatcher = Yii::$app->get('log');
        if (!isset($dispatcher->targets)) {
            return;
        }

        foreach ($dispatcher->targets as $target) {
            // Check for DbTarget or any class that extends it (custom implementations)
            if ($target instanceof DbTarget && $target->enabled) {
                $this->target = $target;
                break;
            }
            // Also check for custom DbTarget implementations that may not extend yii\log\DbTarget
            // but have the same interface (logTable, db properties)
            if ($target->enabled && isset($target->logTable) && isset($target->db)) {
                $this->target = $target;
                break;
            }
        }
    }

    /**
     * Parse level names to level constants
     *
     * @param array $levelNames
     * @return array
     */
    private function parseLevels(array $levelNames): array
    {
        $levels = [];
        foreach ($levelNames as $name) {
            if (isset(self::LEVEL_MAP[$name])) {
                $levels[] = self::LEVEL_MAP[$name];
            }
        }
        return !empty($levels) ? $levels : [Logger::LEVEL_ERROR, Logger::LEVEL_WARNING];
    }

    /**
     * Format database row into output structure
     *
     * @param array $row Database row
     * @param bool $includeTrace
     * @return array
     */
    private function formatRow(array $row, bool $includeTrace): array
    {
        $timestamp = (int) $row['log_time'];

        $formatted = [
            'level' => self::LEVEL_NAMES[$row['level']] ?? 'unknown',
            'level_code' => (int) $row['level'],
            'timestamp' => $timestamp,
            'timestamp_formatted' => date('Y-m-d H:i:s', $timestamp),
            'category' => $row['category'],
            'message' => $row['message'],
            'message_type' => 'string',
        ];

        // Add prefix if available
        if (!empty($row['prefix'])) {
            $formatted['prefix'] = $row['prefix'];
        }

        return $formatted;
    }
}
