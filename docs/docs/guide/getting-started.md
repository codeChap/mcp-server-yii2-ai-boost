# Getting Started

Make your first tool calls with Yii2 AI Boost.

## Starting the MCP Server

In most cases, your IDE starts the MCP server automatically. For manual testing:

```bash
php yii boost/mcp
```

The server listens on STDIN for JSON-RPC requests and outputs responses to STDOUT.

## Your First Tool Call

All tool calls follow this JSON-RPC 2.0 format:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "application_info",
    "arguments": {
      "include": ["version", "environment"]
    }
  }
}
```

## Common Tool Calls

### 1. Get Application Info

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "application_info",
    "arguments": {
      "include": ["version", "environment", "modules"]
    }
  }
}
```

### 2. List Database Tables

```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "database_schema",
    "arguments": {
      "include": ["tables"]
    }
  }
}
```

### 3. Inspect a Table Schema

```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "database_schema",
    "arguments": {
      "table": "user",
      "include": ["schema"]
    }
  }
}
```

### 4. View All Routes

```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tools/call",
  "params": {
    "name": "route_inspector",
    "arguments": {}
  }
}
```

### 5. Get Recent Error Logs

```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "tools/call",
  "params": {
    "name": "log_inspector",
    "arguments": {
      "target": "all",
      "levels": ["error", "warning"],
      "limit": 50
    }
  }
}
```

### 6. Search Logs by Keyword

```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "method": "tools/call",
  "params": {
    "name": "log_inspector",
    "arguments": {
      "search": "connection",
      "levels": ["error", "warning"],
      "limit": 100
    }
  }
}
```

## Understanding Tool Responses

All tool responses follow this structure:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Tool output as JSON or formatted text"
      }
    ]
  }
}
```

## Common Parameters

Most tools accept these common parameters:

| Parameter | Type | Purpose |
|-----------|------|---------|
| `db` | string | Database connection name (default: 'db') |
| `limit` | integer | Maximum results to return |
| `include` | array | Specific data to include |

## Error Responses

If a tool call fails, you'll receive:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32603,
    "message": "Internal error",
    "data": {
      "message": "Detailed error message"
    }
  }
}
```

## Next Steps

- Explore the [Log Inspector](tools/log-inspector.md) - most commonly used tool
- Learn about [Database Schema](tools/database-schema.md) for deep inspection
- Check the [Cookbook](../cookbook/index.md) for practical examples
