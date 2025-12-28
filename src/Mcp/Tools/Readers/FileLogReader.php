<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Readers;

use Yii;
use yii\log\FileTarget;
use yii\log\Logger;

/**
 * File log reader
 *
 * Reads logs from FileTarget (text log files)
 */
class FileLogReader implements LogReaderInterface
{
    /**
     * @var FileTarget|null The FileTarget instance
     */
    private ?FileTarget $target = null;

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

    /**
     * Log level short codes from Yii2
     *
     * @var array
     */
    private const LEVEL_CODES = [
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
        'trace' => 'trace',
        'profile' => 'profile',
    ];

    public function __construct()
    {
        $this->findFileTarget();
    }

    public function isAvailable(): bool
    {
        return $this->target !== null && $this->getLogFilePath() !== null;
    }

    public function getSource(): string
    {
        return 'file';
    }

    public function read(array $params): array
    {
        if (!$this->isAvailable()) {
            return [
                'logs' => [],
                'summary' => [
                    'total_available' => 0,
                    'returned' => 0,
                    'sources' => ['file' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'FileTarget not configured or log file not found',
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

            // Read log file
            $lines = $this->readLogFile();
            if (empty($lines)) {
                return [
                    'logs' => [],
                    'summary' => [
                        'total_available' => 0,
                        'returned' => 0,
                        'sources' => ['file' => 0],
                        'levels_found' => [],
                        'time_range' => ['earliest' => null, 'latest' => null],
                    ],
                    'source' => $this->getSource(),
                ];
            }

            // Parse lines into structured entries
            $entries = [];
            foreach ($lines as $line) {
                $entry = $this->parseLine($line);
                if ($entry !== null && $this->matchesFilter($entry, $levels, $categories, $search, $timeRange)) {
                    $entries[] = $entry;
                }
            }

            // Reverse to show newest first
            $entries = array_reverse($entries);

            // Get total before pagination
            $total = count($entries);

            // Apply pagination
            $paginated = array_slice($entries, $offset, $limit);

            // Format output
            $logs = [];
            $earliestTime = null;
            $latestTime = null;
            $levelsFound = [];

            foreach ($paginated as $entry) {
                $log = $this->formatEntry($entry, $includeTrace);
                $logs[] = $log;

                // Track time range and levels
                if ($latestTime === null || $entry['timestamp'] > $latestTime) {
                    $latestTime = $entry['timestamp'];
                }
                if ($earliestTime === null || $entry['timestamp'] < $earliestTime) {
                    $earliestTime = $entry['timestamp'];
                }
                $levelsFound[$entry['level']] = true;
            }

            return [
                'logs' => $logs,
                'summary' => [
                    'total_available' => $total,
                    'returned' => count($logs),
                    'sources' => ['file' => count($logs)],
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
                    'sources' => ['file' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'Failed to read log file: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Find FileTarget in configured targets
     *
     * @return void
     */
    private function findFileTarget(): void
    {
        if (!Yii::$app->has('log')) {
            return;
        }

        $dispatcher = Yii::$app->get('log');
        if (!isset($dispatcher->targets)) {
            return;
        }

        foreach ($dispatcher->targets as $target) {
            if ($target instanceof FileTarget && $target->enabled) {
                $this->target = $target;
                break;
            }
        }
    }

    /**
     * Get the log file path
     *
     * @return string|null
     */
    private function getLogFilePath(): ?string
    {
        if ($this->target === null) {
            return null;
        }

        $logFile = Yii::getAlias($this->target->logFile);
        return file_exists($logFile) ? $logFile : null;
    }

    /**
     * Read log file, preferring tail to avoid large file issues
     *
     * @return array Lines from log file
     */
    private function readLogFile(): array
    {
        $logFile = $this->getLogFilePath();
        if ($logFile === null) {
            return [];
        }

        // Get file size
        $fileSize = filesize($logFile);

        // For large files, use tail to get recent entries
        if ($fileSize > 5 * 1024 * 1024) {  // 5MB+
            return $this->readFileTail($logFile, 5000);
        }

        // For smaller files, read entire file
        $content = file_get_contents($logFile);
        if ($content === false) {
            return [];
        }

        return array_filter(explode("\n", $content), fn($line) => !empty(trim($line)));
    }

    /**
     * Read last N lines of file using tail
     *
     * @param string $file File path
     * @param int $lines Number of lines to read
     * @return array
     */
    private function readFileTail(string $file, int $lines): array
    {
        // Use tail command if available (Unix-like systems)
        if (function_exists('shell_exec')) {
            $output = @shell_exec("tail -n $lines " . escapeshellarg($file) . " 2>/dev/null");
            if ($output !== null) {
                return array_filter(explode("\n", $output), fn($line) => !empty(trim($line)));
            }
        }

        // Fallback: read file chunks from the end
        $handle = @fopen($file, 'r');
        if ($handle === false) {
            return [];
        }

        $result = [];
        $buffer = '';
        $chunkSize = 8192;
        $lineCount = 0;

        fseek($handle, 0, SEEK_END);
        $fileSize = ftell($handle);
        $position = $fileSize;

        while ($position >= 0 && $lineCount < $lines) {
            $position = max(0, $position - $chunkSize);
            fseek($handle, $position);
            $chunk = fread($handle, min($chunkSize, $fileSize - $position));

            if ($chunk === false) {
                break;
            }

            $buffer = $chunk . $buffer;
            $parts = explode("\n", $buffer);
            $buffer = $parts[0];

            for ($i = count($parts) - 1; $i >= 1; $i--) {
                if (!empty(trim($parts[$i]))) {
                    array_unshift($result, $parts[$i]);
                    $lineCount++;
                    if ($lineCount >= $lines) {
                        break;
                    }
                }
            }
        }

        fclose($handle);
        return $result;
    }

    /**
     * Parse a single log line
     *
     * Format: [timestamp] [level] [category] [prefix] message
     * Example: [2023-12-19 14:00:00.123] [error] [yii\db\Connection] [request-id] Connection refused
     *
     * @param string $line
     * @return array|null
     */
    private function parseLine(string $line): ?array
    {
        // Match log line format: [timestamp] [level] [category] [prefix] message
        if (!preg_match(
            '/^\[([^\]]+)\]\s+\[([^\]]+)\]\s+\[([^\]]+)\]\s*(?:\[([^\]]+)\])?\s+(.*)/s',
            $line,
            $matches
        )) {
            return null;
        }

        $timestampStr = $matches[1];
        $levelStr = $matches[2];
        $category = $matches[3];
        $prefix = $matches[4] ?? null;
        $message = $matches[5];

        // Convert level string to code
        $level = $this->getLevelCode($levelStr);
        if ($level === null) {
            return null;
        }

        // Parse timestamp
        $timestamp = $this->parseTimestamp($timestampStr);
        if ($timestamp === null) {
            return null;
        }

        return [
            'timestamp' => $timestamp,
            'level' => $levelStr,
            'level_code' => $level,
            'category' => $category,
            'prefix' => $prefix,
            'message' => $message,
        ];
    }

    /**
     * Get level code from level string
     *
     * @param string $levelStr
     * @return int|null
     */
    private function getLevelCode(string $levelStr): ?int
    {
        $levelStr = strtolower($levelStr);
        return self::LEVEL_MAP[$levelStr] ?? null;
    }

    /**
     * Parse timestamp string to Unix timestamp
     *
     * @param string $timestampStr
     * @return float|null
     */
    private function parseTimestamp(string $timestampStr): ?float
    {
        // Handle both with and without microseconds
        $pattern = '/^(\d{4})-(\d{2})-(\d{2})\s+(\d{2}):(\d{2}):(\d{2})(?:\.(\d+))?$/';
        if (!preg_match($pattern, $timestampStr, $matches)) {
            return null;
        }

        $year = (int) $matches[1];
        $month = (int) $matches[2];
        $day = (int) $matches[3];
        $hour = (int) $matches[4];
        $minute = (int) $matches[5];
        $second = (int) $matches[6];
        $microseconds = !empty($matches[7]) ? (float) ('0.' . $matches[7]) : 0.0;

        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
        return $timestamp !== false ? $timestamp + $microseconds : null;
    }

    /**
     * Check if entry matches all filters
     *
     * @param array $entry
     * @param array $levels
     * @param array $categories
     * @param string|null $search
     * @param array|null $timeRange
     * @return bool
     */
    private function matchesFilter(
        array $entry,
        array $levels,
        array $categories,
        ?string $search,
        ?array $timeRange
    ): bool {
        // Filter by level
        if (!in_array($entry['level_code'], $levels, true)) {
            return false;
        }

        // Filter by category (wildcard support)
        if (!$this->matchesCategory($entry['category'], $categories)) {
            return false;
        }

        // Filter by search term (case-insensitive)
        if ($search !== null && stripos($entry['message'], $search) === false) {
            return false;
        }

        // Filter by time range
        if ($timeRange !== null) {
            $timestamp = $entry['timestamp'];
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

            // Convert wildcard pattern to simple string matching
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
     * Format entry into output structure
     *
     * @param array $entry
     * @param bool $includeTrace
     * @return array
     */
    private function formatEntry(array $entry, bool $includeTrace): array
    {
        $formatted = [
            'level' => $entry['level'],
            'level_code' => $entry['level_code'],
            'timestamp' => $entry['timestamp'],
            'timestamp_formatted' => date('Y-m-d H:i:s', (int) $entry['timestamp']),
            'category' => $entry['category'],
            'message' => $entry['message'],
            'message_type' => 'string',
        ];

        // Add prefix if available
        if (!empty($entry['prefix'])) {
            $formatted['prefix'] = $entry['prefix'];
        }

        return $formatted;
    }
}
