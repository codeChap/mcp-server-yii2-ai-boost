# Database Schema Tool

Inspect your database structure including tables, columns, indexes, and models.

## Overview

The Database Schema tool provides complete introspection into your database structure and Active Record models.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `db` | string | `db` | Database connection name |
| `table` | string | null | Specific table to inspect |
| `include` | array | `["tables", "schema"]` | What to include |

### Include Options

- `tables` - List all tables with row counts
- `schema` - Detailed table schema (requires table parameter)
- `indexes` - Indexes for a table (requires table parameter)
- `models` - Discovered Active Record models
- `all` - Include everything

## Example Requests

### List All Tables

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["tables"]
  }
}
```

### Get Table Schema

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["schema"]
  }
}
```

### Get Table Indexes

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["indexes"]
  }
}
```

### Discover Active Record Models

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["models"]
  }
}
```

### Complete Database Inspection

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["all"]
  }
}
```

## Example Response

```json
{
  "tables": {
    "user": {
      "name": "user",
      "row_count": 42
    },
    "post": {
      "name": "post",
      "row_count": 156
    }
  },
  "schema": {
    "table": "user",
    "columns": {
      "id": {
        "name": "id",
        "type": "integer",
        "db_type": "int(11)",
        "php_type": "integer",
        "not_null": true,
        "autoIncrement": true
      },
      "username": {
        "name": "username",
        "type": "string",
        "db_type": "varchar(255)",
        "php_type": "string",
        "not_null": true,
        "default": null
      }
    },
    "primary_key": ["id"],
    "foreign_keys": []
  },
  "models": [
    "app\\models\\User",
    "app\\models\\Post",
    "app\\models\\Comment"
  ]
}
```

## Use Cases

- Understand database structure
- Find table relationships
- Discover Active Record models
- Check row counts
- Analyze schema design
