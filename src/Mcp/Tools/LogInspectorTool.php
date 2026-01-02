<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use codechap\yii2boost\Mcp\Tools\Base\BaseTool;
use codechap\yii2boost\Mcp\Tools\Readers\DbLogReader;
use codechap\yii2boost\Mcp\Tools\Readers\FileLogReader;
use codechap\yii2boost\Mcp\Tools\Readers\InMemoryLogReader;
use codechap\yii2boost\Mcp\Tools\Readers\LogReaderInterface;

/**
 * Log Inspector Tool
 *
 * Provides unified access to application logs from all configured targets
 * (FileTarget, DbTarget, and in-memory logs), with support for filtering
 * by level, category, time range, and keyword search.
 */
final class LogInspectorTool extends BaseTool
{
    public function getName(): string
    {
        return 'log_inspector';
    }

    public function getDescription(): string
    {
        return 'Inspect application logs from all configured targets (file, database, memory) ' .
            'with filtering by level, category, time range, and keywords';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'target' => [
                    'type' => 'string',
                    'description' => 'Log source: all, file, db, memory (default: all)',
                    'enum' => ['all', 'file', 'db', 'memory'],
                ],
                'levels' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                        'enum' => ['error', 'warning', 'info', 'trace', 'profile'],
                    ],
                    'description' => 'Log levels to include (default: error, warning)',
                ],
                'categories' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Category patterns to match (supports wildcards like ' .
                        'yii\\db\\*). Default: all categories',
                ],
                'limit' => [
                    'type' => 'integer',
                    'description' => 'Maximum log entries to return (default: 100, max: 1000)',
                    'minimum' => 1,
                    'maximum' => 1000,
                ],
                'offset' => [
                    'type' => 'integer',
                    'description' => 'Number of entries to skip for pagination (default: 0)',
                    'minimum' => 0,
                ],
                'search' => [
                    'type' => 'string',
                    'description' => 'Search for keyword in log messages (case-insensitive)',
                ],
                'time_range' => [
                    'type' => 'object',
                    'properties' => [
                        'start' => [
                            'type' => 'integer',
                            'description' => 'Start timestamp (Unix epoch)',
                        ],
                        'end' => [
                            'type' => 'integer',
                            'description' => 'End timestamp (Unix epoch)',
                        ],
                    ],
                    'description' => 'Filter logs within a time range',
                ],
                'include_trace' => [
                    'type' => 'boolean',
                    'description' => 'Include stack traces for in-memory logs (default: false)',
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $target = $arguments['target'] ?? 'all';
        $levels = $arguments['levels'] ?? ['error', 'warning'];
        $categories = $arguments['categories'] ?? ['*'];
        $limit = min((int) ($arguments['limit'] ?? 100), 1000);
        $offset = (int) ($arguments['offset'] ?? 0);
        $search = $arguments['search'] ?? null;
        $timeRange = $arguments['time_range'] ?? null;
        $includeTrace = (bool) ($arguments['include_trace'] ?? false);

        // Prepare parameters for readers
        // We request (limit + offset) items from each reader starting at offset 0.
        // This is necessary because we can't know which source has the relevant logs
        // for the requested page without fetching and merging them first.
        // We apply the actual offset and limit AFTER merging and sorting.
        $fetchLimit = $limit + $offset;
        $readerParams = [
            'levels' => $levels,
            'categories' => $categories,
            'limit' => $fetchLimit,
            'offset' => 0, // Always fetch from start to ensure correct global ordering
            'search' => $search,
            'time_range' => $timeRange,
            'include_trace' => $includeTrace,
        ];

        // Get readers based on target
        $readers = $this->getReaders($target);

        // Collect logs from all available readers
        $allLogs = [];
        $totalAvailable = 0;
        $targetsQueried = [];
        $warnings = [];

        foreach ($readers as $reader) {
            if (!$reader->isAvailable()) {
                $warnings[] = ucfirst($reader->getSource()) . ' logs not available';
                continue;
            }

            $result = $reader->read($readerParams);
            $targetsQueried[] = $reader->getSource();

            if (isset($result['error'])) {
                $warnings[] = ucfirst($reader->getSource()) . ': ' . $result['error'];
                continue;
            }

            $allLogs = array_merge($allLogs, $result['logs'] ?? []);

            // Sum up total available logs from all sources
            if (isset($result['summary']['total_available'])) {
                $totalAvailable += (int)$result['summary']['total_available'];
            }
        }

        // Sort all logs by timestamp descending
        usort($allLogs, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // Re-apply limit and offset across all sources
        $paginatedLogs = array_slice($allLogs, $offset, $limit);

        // Build aggregated summary
        $summary = $this->buildSummary($paginatedLogs, $allLogs, $targetsQueried, $totalAvailable);

        return $this->sanitize([
            'logs' => $paginatedLogs,
            'summary' => $summary,
            'targets_queried' => $targetsQueried,
            'warnings' => !empty($warnings) ? $warnings : [],
        ]);
    }

    /**
     * Get appropriate readers based on target parameter
     *
     * @param string $target
     * @return LogReaderInterface[]
     */
    private function getReaders(string $target): array
    {
        $readers = [];

        if ($target === 'all' || $target === 'memory') {
            $readers[] = new InMemoryLogReader();
        }
        if ($target === 'all' || $target === 'file') {
            $readers[] = new FileLogReader();
        }
        if ($target === 'all' || $target === 'db') {
            $readers[] = new DbLogReader();
        }

        return $readers;
    }

    /**
     * Build aggregated summary from all sources
     *
     * @param array $paginatedLogs Logs after pagination
     * @param array $allLogs All logs before pagination
     * @param array $targetsQueried Targets that were queried
     * @param int $totalAvailable Total logs available across all sources
     * @return array
     */
    private function buildSummary(
        array $paginatedLogs,
        array $allLogs,
        array $targetsQueried,
        int $totalAvailable
    ): array {
        $levelsFound = [];
        $earliestTime = null;
        $latestTime = null;
        $sourceCount = [];

        // Count by level and time range
        foreach ($allLogs as $log) {
            $levelsFound[$log['level']] = true;

            if ($latestTime === null || $log['timestamp'] > $latestTime) {
                $latestTime = $log['timestamp'];
            }
            if ($earliestTime === null || $log['timestamp'] < $earliestTime) {
                $earliestTime = $log['timestamp'];
            }

            $source = $log['source'] ?? 'unknown';
            $sourceCount[$source] = ($sourceCount[$source] ?? 0) + 1;
        }

        return [
            'total_available' => $totalAvailable,
            'returned' => count($paginatedLogs),
            'sources' => $sourceCount,
            'levels_found' => array_keys($levelsFound),
            'time_range' => [
                'earliest' => $earliestTime,
                'latest' => $latestTime,
            ],
        ];
    }
}
