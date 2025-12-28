<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Readers;

use Yii;
use yii\log\Logger;

/**
 * In-memory log reader
 *
 * Reads logs from Yii::getLogger()->messages (current request only)
 */
class InMemoryLogReader implements LogReaderInterface
{
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

    public function isAvailable(): bool
    {
        return true;  // Always available
    }

    public function getSource(): string
    {
        return 'memory';
    }

    public function read(array $params): array
    {
        $logger = Yii::getLogger();
        $messages = $logger->messages;

        // Parse parameters
        $levels = $this->parseLevels($params['levels'] ?? ['error', 'warning']);
        $categories = $params['categories'] ?? ['*'];
        $limit = (int) ($params['limit'] ?? 100);
        $offset = (int) ($params['offset'] ?? 0);
        $search = $params['search'] ?? null;
        $timeRange = $params['time_range'] ?? null;
        $includeTrace = (bool) ($params['include_trace'] ?? false);

        // Filter messages
        $filtered = [];
        foreach ($messages as $message) {
            if ($this->matchesFilter($message, $levels, $categories, $search, $timeRange)) {
                $filtered[] = $message;
            }
        }

        // Sort by timestamp descending (newest first)
        usort($filtered, fn($a, $b) => $b[3] <=> $a[3]);

        // Get total before pagination
        $total = count($filtered);

        // Apply pagination
        $paginated = array_slice($filtered, $offset, $limit);

        // Format output
        $logs = [];
        $earliestTime = null;
        $latestTime = null;

        foreach ($paginated as $message) {
            $log = $this->formatMessage($message, $includeTrace);
            $logs[] = $log;

            if ($latestTime === null || $log['timestamp'] > $latestTime) {
                $latestTime = $log['timestamp'];
            }
            if ($earliestTime === null || $log['timestamp'] < $earliestTime) {
                $earliestTime = $log['timestamp'];
            }
        }

        return [
            'logs' => $logs,
            'summary' => [
                'total_available' => $total,
                'returned' => count($logs),
                'sources' => ['memory' => $total],
                'levels_found' => array_values($this->extractLevelsFromFiltered($filtered)),
                'time_range' => [
                    'earliest' => $earliestTime,
                    'latest' => $latestTime,
                ],
            ],
            'source' => $this->getSource(),
        ];
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
     * Check if message matches all filters
     *
     * @param array $message
     * @param array $levels
     * @param array $categories
     * @param string|null $search
     * @param array|null $timeRange
     * @return bool
     */
    private function matchesFilter(
        array $message,
        array $levels,
        array $categories,
        ?string $search,
        ?array $timeRange
    ): bool {
        // Filter by level
        if (!in_array($message[1], $levels, true)) {
            return false;
        }

        // Filter by category (wildcard support)
        if (!$this->matchesCategory($message[2], $categories)) {
            return false;
        }

        // Filter by search term (case-insensitive)
        if ($search !== null) {
            $messageText = is_string($message[0]) ? $message[0] : json_encode($message[0]);
            if (stripos($messageText, $search) === false) {
                return false;
            }
        }

        // Filter by time range
        if ($timeRange !== null) {
            $timestamp = $message[3];
            if (isset($timeRange['start']) && $timestamp < $timeRange['start']) {
                return false;
            }
            if (isset($timeRange['end']) && $timestamp > $timeRange['end']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if category matches patterns (supports wildcards)
     *
     * @param string $category
     * @param array $patterns
     * @return bool
     */
    private function matchesCategory(string $category, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if ($pattern === '*') {
                return true;
            }

            // Convert wildcard pattern to regex
            if (str_ends_with($pattern, '*')) {
                $prefix = rtrim($pattern, '*');
                if (str_starts_with($category, $prefix)) {
                    return true;
                }
            } elseif ($pattern === $category) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract unique level names from filtered messages
     *
     * @param array $filtered
     * @return array
     */
    private function extractLevelsFromFiltered(array $filtered): array
    {
        $levels = [];
        foreach ($filtered as $message) {
            $levelCode = $message[1];
            if (isset(self::LEVEL_NAMES[$levelCode])) {
                $levels[self::LEVEL_NAMES[$levelCode]] = true;
            }
        }
        return array_keys($levels);
    }

    /**
     * Format a message into output structure
     *
     * @param array $message
     * @param bool $includeTrace
     * @return array
     */
    private function formatMessage(array $message, bool $includeTrace): array
    {
        $timestamp = $message[3];

        $formatted = [
            'level' => self::LEVEL_NAMES[$message[1]] ?? 'unknown',
            'level_code' => $message[1],
            'timestamp' => $timestamp,
            'timestamp_formatted' => $this->formatTimestamp($timestamp),
            'category' => $message[2],
            'message' => is_string($message[0]) ? $message[0] : json_encode($message[0]),
            'message_type' => is_string($message[0]) ? 'string' : (is_array($message[0]) ? 'array' : 'other'),
        ];

        // Add memory usage if available (v2.0.11+)
        if (isset($message[5])) {
            $formatted['memory_usage'] = $message[5];
        }

        // Add trace if requested and available
        if ($includeTrace && isset($message[4]) && is_array($message[4])) {
            $formatted['trace'] = $this->formatTrace($message[4]);
        }

        return $formatted;
    }

    /**
     * Format timestamp to ISO 8601
     *
     * @param float $timestamp
     * @return string
     */
    private function formatTimestamp(float $timestamp): string
    {
        return date('Y-m-d H:i:s', (int) $timestamp);
    }

    /**
     * Format trace array
     *
     * @param array $trace
     * @return array
     */
    private function formatTrace(array $trace): array
    {
        return array_map(function ($frame) {
            return [
                'file' => $frame['file'] ?? null,
                'line' => $frame['line'] ?? null,
                'function' => $frame['function'] ?? null,
                'class' => $frame['class'] ?? null,
            ];
        }, $trace);
    }
}
