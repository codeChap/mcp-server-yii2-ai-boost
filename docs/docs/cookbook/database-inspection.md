# Database Inspection

Learn how to thoroughly inspect your database structure and models.

## Overview

The Database Schema tool provides complete visibility into your database structure. Combined with the Log Inspector, you can understand how your data is organized and how it's being accessed.

## Basic Inspection

### Get Database Overview

First, see all tables:

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["tables"]
  }
}
```

This returns all tables with their row counts, giving you a quick overview of your database size.

### Inspect a Specific Table

Once you've found an interesting table, get its schema:

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["schema"]
  }
}
```

This shows:
- All columns with types
- Primary key information
- Nullable fields
- Default values
- Auto-increment status

## Understanding Schema Output

Each column includes:

```json
{
  "name": "id",
  "type": "integer",           // Yii2 type
  "db_type": "int(11)",        // Database type
  "php_type": "integer",       // PHP type
  "size": 11,
  "not_null": true,            // Nullable?
  "default": null,
  "autoIncrement": true,
  "comment": "User ID"
}
```

**Key details:**
- `type` - How Yii2 interprets it
- `db_type` - Raw database definition
- `not_null` - Is this required?
- `default` - What's the default value?
- `autoIncrement` - Does it auto-increment?

## Finding Relationships

### Get Indexes and Foreign Keys

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "post",
    "include": ["schema", "indexes"]
  }
}
```

Look for:
- **Indexes** - Which columns are indexed (performance)
- **Foreign keys** - Relationships to other tables

## Discovering Models

### Find Active Record Models

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["models"]
  }
}
```

This discovers all Active Record model classes in your `@app/models` directory.

## Advanced Analysis

### Complete Database Inspection

Get everything about a table:

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["all"]
  }
}
```

This returns:
- All tables with row counts
- Complete schema for the specified table
- Indexes and constraints
- Discovered models

## Common Scenarios

### Scenario: "What tables do we have?"

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["tables"]
  }
}
```

Check the response for:
- Number of tables
- Row counts
- Naming patterns

### Scenario: "How do users relate to posts?"

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "post",
    "include": ["schema"]
  }
}
```

Look in the response for:
- A `user_id` column
- Type should be integer matching `user.id`
- Check if it's indexed

### Scenario: "Are there unused tables?"

```json
{
  "name": "database_schema",
  "arguments": {
    "include": ["tables"]
  }
}
```

Check for:
- Tables with 0 rows
- Tables with unusual names
- Tables that don't match models

### Scenario: "What columns can be indexed better?"

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "order",
    "include": ["schema", "indexes"]
  }
}
```

Look for:
- Columns frequently used in WHERE clauses without indexes
- Foreign key columns without indexes
- Large tables with few indexes

## Combining with Logs

Use database inspection with log inspection to understand performance:

1. Find slow queries in logs:
```json
{
  "name": "log_inspector",
  "arguments": {
    "categories": ["yii\\db\\*"],
    "search": "slow",
    "limit": 50
  }
}
```

2. Check the affected table schema:
```json
{
  "name": "database_schema",
  "arguments": {
    "table": "affected_table",
    "include": ["schema", "indexes"]
  }
}
```

3. Identify missing indexes or optimization opportunities

## Multiple Database Connections

If you have multiple database connections:

```json
{
  "name": "database_schema",
  "arguments": {
    "db": "replica",
    "table": "user",
    "include": ["schema"]
  }
}
```

Use the `db` parameter to inspect specific connections.

## Migration Planning

Use schema inspection when planning migrations:

1. Check current structure:
```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["schema"]
  }
}
```

2. Plan your changes
3. Check for dependent relationships
4. Review indexes that might be affected

## Performance Analysis

### Tables Needing Indexes

```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user_activity",
    "include": ["schema", "indexes"]
  }
}
```

Then check logs for queries on unindexed columns:

```json
{
  "name": "log_inspector",
  "arguments": {
    "categories": ["yii\\db\\*"],
    "levels": ["warning"],
    "search": "user_activity"
  }
}
```

## Best Practices

1. **Run regularly** - Database structure changes with migrations
2. **Check row counts** - Understand data volume
3. **Verify relationships** - Ensure foreign keys exist
4. **Monitor indexes** - Sufficient but not excessive
5. **Match models** - Ensure all tables have models

## Next Steps

- Learn about [model relationships](../guide/tools/database-schema.md)
- Use [Log Inspector](debugging-with-logs.md) to find slow queries
- Check [Routes](finding-routes.md) to understand API endpoints
