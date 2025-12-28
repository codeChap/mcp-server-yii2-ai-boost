# Log Inspector Tool

Inspect application logs from all configured sources with flexible filtering.

## Overview

The Log Inspector tool provides unified access to logs from multiple sources:
- **In-Memory Logs** - Current request with full stack traces
- **File Logs** - Text-based logs with automatic file rotation handling
- **Database Logs** - Persistent logs with fast queries

All sources can be queried with a single tool call.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `target` | string | `all` | Log source: `all`, `file`, `db`, `memory` |
| `levels` | array | `["error", "warning"]` | Log levels to include |
| `categories` | array | `["*"]` | Category patterns (wildcard support) |
| `limit` | integer | 100 | Max entries to return (max: 1000) |
| `offset` | integer | 0 | Pagination offset |
| `search` | string | null | Keyword search in messages |
| `time_range` | object | null | Time range filter |
| `include_trace` | boolean | false | Include stack traces (in-memory only) |

### Log Levels

- `error` - Error messages
- `warning` - Warning messages
- `info` - Informational messages
- `trace` - Code execution flow
- `profile` - Performance profiling

### Time Range Format

```json
{
  "start": 1703000000,
  "end": 1703086400
}
```

Values are Unix timestamps (seconds since epoch).

## Example Requests

### Get Recent Errors

```json
{
  "name": "log_inspector",
  "arguments": {
    "levels": ["error"],
    "limit": 50
  }
}
```

### Search Database Logs

```json
{
  "name": "log_inspector",
  "arguments": {
    "target": "db",
    "categories": ["yii\\db\\*"],
    "levels": ["error", "warning"],
    "limit": 100
  }
}
```

### Keyword Search

```json
{
  "name": "log_inspector",
  "arguments": {
    "search": "connection",
    "levels": ["error", "warning"],
    "limit": 200
  }
}
```

### Time Range Analysis

```json
{
  "name": "log_inspector",
  "arguments": {
    "time_range": {
      "start": 1703000000,
      "end": 1703086400
    },
    "levels": ["error"],
    "limit": 500
  }
}
```

### Custom Categories

```json
{
  "name": "log_inspector",
  "arguments": {
    "categories": ["app\\models\\*", "app\\controllers\\*"],
    "levels": ["error", "warning", "info"],
    "limit": 100
  }
}
```

### In-Memory with Stack Traces

```json
{
  "name": "log_inspector",
  "arguments": {
    "target": "memory",
    "levels": ["error"],
    "include_trace": true,
    "limit": 50
  }
}
```

## Example Response

```json
{
  "logs": [
    {
      "level": "error",
      "level_code": 1,
      "timestamp": 1703000000.123,
      "timestamp_formatted": "2023-12-19 14:00:00",
      "category": "yii\\db\\Connection",
      "message": "Connection refused",
      "message_type": "string",
      "source": "file",
      "prefix": "request-id-123",
      "memory_usage": 2097152,
      "trace": [
        {
          "file": "/app/models/User.php",
          "line": 42,
          "function": "save",
          "class": "app\\models\\User"
        }
      ]
    }
  ],
  "summary": {
    "total_available": 1024,
    "returned": 50,
    "sources": {
      "file": 512,
      "db": 512,
      "memory": 0
    },
    "levels_found": ["error", "warning"],
    "time_range": {
      "earliest": 1703000000,
      "latest": 1703086400
    }
  },
  "targets_queried": ["file", "db"],
  "warnings": []
}
```

## Reader Types

### In-Memory Reader
- **Source**: Current request logs from `Yii::getLogger()->messages`
- **Features**: Full stack traces, microsecond precision, instant access
- **Limitations**: Only current request, cleared on shutdown

### File Reader
- **Source**: FileTarget text logs (default: `@runtime/logs/app.log`)
- **Features**: Handles large files efficiently, auto-detects rotation
- **Limitations**: Text parsing required, no indexed search

### Database Reader
- **Source**: DbTarget table (default: `{{%log}}`)
- **Features**: Fast indexed queries, precise time filtering, optimal for volume
- **Limitations**: Requires table setup

## Performance Tips

- Use `limit` parameter to prevent huge responses
- Use `time_range` to filter early
- Create database indexes on `level`, `category`, `log_time` for DbTarget
- Use `target` to query specific sources instead of `all`

## Database Setup

To enable database logging, configure:

```php
'log' => [
    'targets' => [
        'db' => [
            'class' => 'yii\log\DbTarget',
            'levels' => ['error', 'warning'],
            'logTable' => '{{%log}}',
            'db' => 'db',
        ],
    ],
],
```

Run migration to create log table:

```bash
php yii migrate --migrationPath=@yii/log/migrations
```

## Use Cases

- Debug recent errors
- Search for specific failures
- Analyze logs by category
- Investigate time-specific issues
- Find application performance bottlenecks
- Track user activity
