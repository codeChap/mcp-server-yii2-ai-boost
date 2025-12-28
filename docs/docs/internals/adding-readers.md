# Adding Readers

Extend the Log Inspector with custom log readers for new storage backends.

## Reader Architecture

The Log Inspector uses a multi-reader pattern for accessing logs from different sources.

### Three Built-In Readers

1. **InMemoryLogReader** - Current request logs
2. **FileLogReader** - Text file logs
3. **DbLogReader** - Database logs

### How They Work

```
LogInspectorTool::execute()
    ├─ Discovers available readers
    ├─ Queries each reader independently
    ├─ Merges results
    ├─ Sorts by timestamp
    └─ Returns aggregated summary
```

## Creating a Custom Reader

### Step 1: Implement LogReaderInterface

Create `src/Mcp/Tools/Readers/MyCustomLogReader.php`:

```php
<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Readers;

use yii\log\Logger;

class MyCustomLogReader implements LogReaderInterface
{
    /**
     * Level constant mapping
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
        // Check if this reader can operate
        // For example, check if service is configured
        return true;
    }

    public function getSource(): string
    {
        return 'my_custom_source';  // Identifier for results
    }

    public function read(array $params): array
    {
        try {
            // Parse parameters
            $levels = $this->parseLevels($params['levels'] ?? ['error', 'warning']);
            $categories = $params['categories'] ?? ['*'];
            $limit = (int) ($params['limit'] ?? 100);
            $offset = (int) ($params['offset'] ?? 0);
            $search = $params['search'] ?? null;
            $timeRange = $params['time_range'] ?? null;

            // Fetch logs from your source
            $logs = $this->fetchLogs($levels, $categories, $search, $timeRange);

            // Sort by timestamp (newest first)
            usort($logs, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

            // Get total before pagination
            $total = count($logs);

            // Apply pagination
            $paginated = array_slice($logs, $offset, $limit);

            // Build response
            $levelsFound = [];
            $earliestTime = null;
            $latestTime = null;

            foreach ($paginated as $log) {
                $levelsFound[$log['level']] = true;
                
                if ($latestTime === null || $log['timestamp'] > $latestTime) {
                    $latestTime = $log['timestamp'];
                }
                if ($earliestTime === null || $log['timestamp'] < $earliestTime) {
                    $earliestTime = $log['timestamp'];
                }
            }

            return [
                'logs' => $paginated,
                'summary' => [
                    'total_available' => $total,
                    'returned' => count($paginated),
                    'sources' => [$this->getSource() => count($paginated)],
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
                    'sources' => [$this->getSource() => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'Failed to read logs: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Fetch logs from your source
     */
    private function fetchLogs(
        array $levels,
        array $categories,
        ?string $search,
        ?array $timeRange
    ): array {
        // Implement your log fetching logic
        // Return array of log entries
        return [];
    }

    /**
     * Parse level names to level constants
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
}
```

### Step 2: Register Reader

Edit `src/Mcp/Tools/LogInspectorTool.php`, find `getReaders()` method:

```php
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
    if ($target === 'all' || $target === 'my_custom') {
        $readers[] = new MyCustomLogReader();
    }

    return $readers;
}
```

### Step 3: Test Reader

Call Log Inspector with your new reader:

```bash
php yii boost/mcp <<'EOF'
{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"log_inspector","arguments":{"target":"my_custom","limit":50}}}
EOF
```

Check response for your reader's data.

## Example: Sentry Reader

Log to Sentry (error tracking service):

```php
<?php

namespace codechap\yii2boost\Mcp\Tools\Readers;

use Yii;
use yii\log\Logger;

class SentryLogReader implements LogReaderInterface
{
    private const LEVEL_NAMES = [
        Logger::LEVEL_ERROR => 'error',
        Logger::LEVEL_WARNING => 'warning',
        // ...
    ];

    public function isAvailable(): bool
    {
        // Check if Sentry SDK is installed and configured
        return class_exists('Sentry\SentrySDK') && Sentry\SentrySDK::getCurrentHub() !== null;
    }

    public function getSource(): string
    {
        return 'sentry';
    }

    public function read(array $params): array
    {
        try {
            $levels = $this->parseLevels($params['levels'] ?? ['error', 'warning']);
            $limit = (int) ($params['limit'] ?? 100);
            
            // Query Sentry API (simplified example)
            $sentry = Sentry\SentrySDK::getCurrentHub();
            $lastEventId = $sentry->getLastEventId();
            
            // In practice, you'd query the Sentry API or database
            // to fetch actual events
            
            return [
                'logs' => [],  // Fetched from Sentry
                'summary' => [
                    'total_available' => 0,
                    'returned' => 0,
                    'sources' => ['sentry' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
            ];
        } catch (\Exception $e) {
            return [
                'logs' => [],
                'summary' => [
                    'total_available' => 0,
                    'returned' => 0,
                    'sources' => ['sentry' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'Failed to fetch from Sentry: ' . $e->getMessage(),
            ];
        }
    }

    // ... implement other methods ...
}
```

## Example: CloudWatch Reader

Log to AWS CloudWatch:

```php
<?php

namespace codechap\yii2boost\Mcp\Tools\Readers;

use Aws\CloudWatchLogs\CloudWatchLogsClient;

class CloudWatchLogReader implements LogReaderInterface
{
    private ?CloudWatchLogsClient $client = null;

    public function __construct()
    {
        // Initialize AWS CloudWatch client
        try {
            $this->client = new CloudWatchLogsClient([
                'region' => 'us-east-1',
                'version' => 'latest'
            ]);
        } catch (\Exception $e) {
            // Client not available
        }
    }

    public function isAvailable(): bool
    {
        return $this->client !== null;
    }

    public function getSource(): string
    {
        return 'cloudwatch';
    }

    public function read(array $params): array
    {
        try {
            $logs = [];
            
            // Query CloudWatch logs
            $result = $this->client->filterLogEvents([
                'logGroupName' => '/aws/lambda/my-function',
                'limit' => $params['limit'] ?? 100,
            ]);

            // Transform CloudWatch format to standard format
            foreach ($result['events'] as $event) {
                $logs[] = [
                    'timestamp' => $event['timestamp'] / 1000,  // Convert to seconds
                    'level' => 'info',  // Parse from message
                    'category' => 'cloudwatch',
                    'message' => $event['message'],
                ];
            }

            return [
                'logs' => $logs,
                'summary' => [
                    'total_available' => count($logs),
                    'returned' => count($logs),
                    'sources' => ['cloudwatch' => count($logs)],
                    'levels_found' => ['info'],
                    'time_range' => [
                        'earliest' => $logs[0]['timestamp'] ?? null,
                        'latest' => end($logs)['timestamp'] ?? null,
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
                    'sources' => ['cloudwatch' => 0],
                    'levels_found' => [],
                    'time_range' => ['earliest' => null, 'latest' => null],
                ],
                'source' => $this->getSource(),
                'error' => 'Failed to fetch from CloudWatch: ' . $e->getMessage(),
            ];
        }
    }

    // ... implement other methods ...
}
```

## Log Entry Format

All readers should return logs in this standard format:

```php
[
    'timestamp' => 1703000000.123,   // Unix timestamp with microseconds
    'level' => 'error',              // error, warning, info, trace, profile
    'level_code' => 1,               // Logger::LEVEL_ERROR, etc
    'category' => 'yii\db\Connection',
    'message' => 'Connection failed',
    'message_type' => 'string',      // string, array, or other
    'source' => 'file',              // Reader identifier
    'prefix' => 'request-id',        // Optional context
    'memory_usage' => 2097152,       // Optional memory usage
    'trace' => [                     // Optional stack trace
        [
            'file' => '/app/models/User.php',
            'line' => 42,
            'function' => 'save',
            'class' => 'app\models\User'
        ]
    ]
]
```

## Response Format

All readers must return:

```php
[
    'logs' => [],              // Array of log entries
    'summary' => [             // Aggregated metadata
        'total_available' => 0,
        'returned' => 0,
        'sources' => ['reader_name' => 0],
        'levels_found' => [],
        'time_range' => ['earliest' => null, 'latest' => null],
    ],
    'source' => 'reader_name',
    'error' => null,           // Optional error message
]
```

## Testing Your Reader

### Unit Test Example

```php
<?php

namespace codechap\yii2boost\Tests\Unit\Tools\Readers;

use PHPUnit\Framework\TestCase;
use codechap\yii2boost\Mcp\Tools\Readers\MyCustomLogReader;

class MyCustomLogReaderTest extends TestCase
{
    private MyCustomLogReader $reader;

    protected function setUp(): void
    {
        $this->reader = new MyCustomLogReader();
    }

    public function testIsAvailable(): void
    {
        // Should be available or not based on your setup
        $this->assertTrue($this->reader->isAvailable());
    }

    public function testRead(): void
    {
        $result = $this->reader->read([
            'levels' => ['error'],
            'limit' => 10,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('logs', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('source', $result);
    }

    public function testReadReturnsNormalizedFormat(): void
    {
        $result = $this->reader->read(['limit' => 1]);

        if (!empty($result['logs'])) {
            $log = reset($result['logs']);
            $this->assertArrayHasKey('timestamp', $log);
            $this->assertArrayHasKey('level', $log);
            $this->assertArrayHasKey('category', $log);
            $this->assertArrayHasKey('message', $log);
        }
    }
}
```

## Best Practices

1. **Handle Unavailable Sources** - Return empty results, not errors
2. **Normalize Formats** - Always return standard log entry format
3. **Filter Early** - Apply filters at source when possible
4. **Pagination** - Respect limit and offset parameters
5. **Error Handling** - Return graceful error messages
6. **Performance** - Don't block on slow sources
7. **Memory** - Avoid loading huge result sets

## Common Pitfalls

### Pitfall: Not Implementing Interface

```php
// ❌ Wrong - Missing methods
class MyReader
{
    public function read(array $params): array { }
}

// ✅ Correct
class MyReader implements LogReaderInterface
{
    public function isAvailable(): bool { }
    public function getSource(): string { }
    public function read(array $params): array { }
}
```

### Pitfall: Inconsistent Log Format

```php
// ❌ Wrong - Different format than others
return [
    'id' => 1,
    'msg' => 'error',
    'ts' => time(),
];

// ✅ Correct
return [
    'timestamp' => time(),
    'level' => 'error',
    'category' => 'my-app',
    'message' => 'error message',
];
```

### Pitfall: Ignoring Time Zone

```php
// ❌ Wrong - Wrong time representation
'timestamp' => date('Y-m-d H:i:s'),  // String, not number

// ✅ Correct
'timestamp' => time(),  // Unix timestamp in seconds
// or
'timestamp' => microtime(true);  // With microseconds
```

## Next Steps

- Review [Architecture](architecture.md) for system design
- See [Adding Tools](adding-tools.md) for tool development
- Check [Contributing](contributing.md) for pull request guidelines
