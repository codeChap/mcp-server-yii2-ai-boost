# Debugging with Logs

Master the Log Inspector tool for effective application debugging.

## Getting Started

The Log Inspector is your primary tool for understanding what's happening in your application at runtime.

## Common Debugging Scenarios

### Scenario 1: Find Recent Errors

When something just broke, find the latest errors:

```json
{
  "name": "log_inspector",
  "arguments": {
    "target": "all",
    "levels": ["error"],
    "limit": 50
  }
}
```

This queries all available log sources (file, database, memory) and returns the 50 most recent errors, sorted by timestamp.

**Next Steps:**
- Look at the error messages
- Check the category to understand which component failed
- Use the timestamp to correlate with other events
- Search for related errors with `search` parameter

### Scenario 2: Debug Database Issues

When database operations are failing:

```json
{
  "name": "log_inspector",
  "arguments": {
    "categories": ["yii\\db\\*"],
    "levels": ["error", "warning"],
    "limit": 100
  }
}
```

This shows all database-related errors and warnings.

**What to look for:**
- Connection refused messages
- SQL syntax errors
- Transaction issues
- Query timeouts

### Scenario 3: Search for Specific Errors

When you know part of the error message:

```json
{
  "name": "log_inspector",
  "arguments": {
    "search": "timeout",
    "levels": ["error", "warning"],
    "limit": 200
  }
}
```

The `search` parameter performs case-insensitive substring matching across all log messages.

### Scenario 4: Analyze Logs from Specific Time Period

When you need to investigate what happened during a specific window:

```json
{
  "name": "log_inspector",
  "arguments": {
    "time_range": {
      "start": 1703000000,
      "end": 1703086400
    },
    "levels": ["error", "warning"],
    "limit": 500
  }
}
```

Replace the timestamps with your desired window (Unix timestamps, in seconds).

**Finding Unix timestamps:**
```bash
# Current time
date +%s

# Specific date (macOS/Linux)
date -d "2023-12-19" +%s

# Yesterday
date -d "yesterday" +%s
```

### Scenario 5: Track Application Flow

View all logs including info messages:

```json
{
  "name": "log_inspector",
  "arguments": {
    "levels": ["error", "warning", "info"],
    "limit": 100
  }
}
```

This shows errors, warnings, and informational messages to trace application flow.

### Scenario 6: Get Stack Traces

For in-memory logs, include stack traces for better debugging:

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

Stack traces only work with `target: "memory"` since file and database logs don't store trace information.

## Interpreting Log Entries

Each log entry contains:

```json
{
  "level": "error",              // Severity level
  "level_code": 1,               // Internal code
  "timestamp": 1703000000.123,   // Unix timestamp
  "timestamp_formatted": "2023-12-19 14:00:00",
  "category": "yii\\db\\Connection",  // Which component
  "message": "Connection refused",     // What happened
  "message_type": "string",
  "source": "file",              // Where log came from
  "prefix": "request-id-123",    // Additional context
  "memory_usage": 2097152,       // Memory at log time
  "trace": [                     // Stack trace (if requested)
    {
      "file": "/app/models/User.php",
      "line": 42,
      "function": "save",
      "class": "app\\models\\User"
    }
  ]
}
```

**Key fields to check:**
1. `level` - How serious is this? (error > warning > info)
2. `category` - Which component? (yii\db\*, app\*, etc)
3. `message` - What went wrong?
4. `timestamp` - When did it happen?
5. `trace` - Where in the code did it occur?

## Multi-Source Advantage

The Log Inspector can query from three sources:

| Source | Best For | Speed | Data |
|--------|----------|-------|------|
| **memory** | Current request debugging | Instant | Full traces |
| **file** | Historical logs | Fast | Text-based |
| **db** | Production logs | Medium | Queryable, indexed |

When you use `target: "all"`, it queries all available sources and merges results.

## Performance Tips

1. **Use specific categories**: Instead of all logs, filter to relevant categories
2. **Limit results**: Always set `limit` to prevent huge responses
3. **Use time ranges**: For large log volumes, narrow down by time
4. **Choose target wisely**: Query specific sources instead of `all` when possible

## Common Patterns

### "My app just crashed, what happened?"
```json
{
  "target": "all",
  "levels": ["error"],
  "limit": 10
}
```

### "Requests are slow, why?"
```json
{
  "categories": ["yii\\db\\*"],
  "levels": ["error", "warning"],
  "time_range": {"start": TIME_WHEN_SLOW_STARTED, "end": TIME_WHEN_SLOW_ENDED}
}
```

### "User reported issue at 3pm today"
```json
{
  "time_range": {
    "start": 1703300000,  // 2:00 PM
    "end": 1703310000     // 4:00 PM
  },
  "levels": ["error", "warning"],
  "limit": 100
}
```

### "Database migration failed"
```json
{
  "search": "migration",
  "levels": ["error", "warning"],
  "limit": 50
}
```

## Next Steps

- Learn about [other tools](../guide/tools/index.md)
- Set up [database logging](../guide/tools/log-inspector.md#database-setup) for production
- Check [Log Inspector reference](../guide/tools/log-inspector.md)
