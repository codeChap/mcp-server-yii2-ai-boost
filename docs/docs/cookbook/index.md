# Cookbook

Practical recipes and how-to guides for common debugging and analysis tasks with Yii2 AI Boost.

## Contents

### [Debugging with Logs](debugging-with-logs.md)
Learn how to effectively use the Log Inspector to diagnose application issues, search for errors, and analyze log patterns.

### [Database Inspection](database-inspection.md)
Deep dive into database analysis including schema inspection, table relationships, and model discovery.

### [Finding Routes](finding-routes.md)
Discover and analyze your application's routes, URL patterns, and REST API endpoints.

### [Component Analysis](component-analysis.md)
Understand your application components, their configurations, and how they interact.

### [IDE Setup](ide-setup.md)
Configure Yii2 AI Boost with your favorite IDE for seamless integration.

## Quick Examples

### Find Recent Errors
```json
{
  "name": "log_inspector",
  "arguments": {
    "levels": ["error"],
    "limit": 50
  }
}
```

### Search Logs for Specific Issue
```json
{
  "name": "log_inspector",
  "arguments": {
    "search": "timeout",
    "limit": 100
  }
}
```

### Inspect Database Structure
```json
{
  "name": "database_schema",
  "arguments": {
    "table": "user",
    "include": ["schema", "indexes"]
  }
}
```

### Get All Routes
```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

## Troubleshooting Guide

Can't find what you're looking for? Check these resources:

- **Installation Issues** - See [Installation Guide](../guide/installation.md)
- **Tool Parameters** - See [Guide → Tools](../guide/tools/index.md)
- **Architecture** - See [Tools Architecture](../guide/tools/architecture.md)
