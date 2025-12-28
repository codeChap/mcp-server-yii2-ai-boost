# Component Analysis

Understand your application's components and how they're configured.

## Overview

The Component Inspector tool shows you all registered components and their configurations, helping you understand what services are available in your application.

## Getting Started

### List All Components

See every component:

```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

This returns all registered components with their configurations.

## Understanding Component Information

Each component includes:

```json
{
  "id": "db",
  "class": "yii\\db\\Connection",
  "config": {
    "dsn": "mysql:host=localhost;dbname=myapp",
    "username": "***REDACTED***",
    "password": "***REDACTED***",
    "charset": "utf8"
  },
  "singleton": true,
  "behaviors": []
}
```

- `id` - How to access it (`Yii::$app->db`)
- `class` - The component class
- `config` - Configuration array (sensitive data hidden)
- `singleton` - Whether it's created once and reused
- `behaviors` - Attached behaviors

## Core Components

### Database Connection

```json
{
  "id": "db",
  "class": "yii\\db\\Connection",
  "config": {
    "dsn": "mysql:host=localhost;dbname=myapp",
    "charset": "utf8"
  }
}
```

Use in code: `Yii::$app->db`

### Cache

```json
{
  "id": "cache",
  "class": "yii\\caching\\FileCache",
  "config": {
    "cachePath": "@runtime/cache"
  }
}
```

Use in code: `Yii::$app->cache`

### Logger

```json
{
  "id": "log",
  "class": "yii\\log\\Dispatcher",
  "config": {
    "traceLevel": 3
  }
}
```

Use in code: `Yii::getLogger()`

### Request Handler

```json
{
  "id": "request",
  "class": "yii\\web\\Request",
  "config": {}
}
```

Use in code: `Yii::$app->request`

### Response Handler

```json
{
  "id": "response",
  "class": "yii\\web\\Response",
  "config": {}
}
```

Use in code: `Yii::$app->response`

## Common Scenarios

### Scenario: "What's the cache configuration?"

```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

Find the `cache` component, check:
- Is it FileCache or MemCache or other?
- What's the cache path or server?
- Is it enabled?

### Scenario: "How do I access component X?"

Find the component in the list:
- Check the `id` field
- Access it via `Yii::$app->componentId`

For example:
- Component with `id: "cache"` → `Yii::$app->cache`
- Component with `id: "mailer"` → `Yii::$app->mailer`

### Scenario: "What database is being used?"

```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

Find the `db` component:
- Check `class` for database type (Connection = SQL database)
- Check `config` for connection details (though password is redacted)
- Verify it's a singleton (yes = reused connection)

### Scenario: "Is logging to database enabled?"

```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

Find the `log` component and check:
- What targets are configured?
- Is DbTarget present?
- What levels are being logged?

### Scenario: "Are there custom components?"

Look for components that aren't standard Yii:
- Components not in `yii\*` namespace
- Components in `app\*` namespace
- Check if they're singletons or new instances

## Singleton vs New Instance

```json
{
  "singleton": true   // Created once, same instance every time
}
```

- **Singleton (true)**: Shared resource, usually database, cache, logger
- **New Instance (false)**: New object created each time

Most components are singletons for performance.

## Component Configuration

### Checking Configuration

Sensitive data is automatically redacted:

```json
{
  "config": {
    "password": "***REDACTED***",
    "api_key": "***REDACTED***",
    "secret": "***REDACTED***"
  }
}
```

Safe to share component configurations without exposing secrets.

### Understanding Config Values

```json
{
  "cachePath": "@runtime/cache"  // Yii alias for application path
}
```

Aliases like `@runtime`, `@app`, `@web` are translated to actual paths.

## Behaviors

Check if components have behaviors attached:

```json
{
  "behaviors": [
    {
      "class": "yii\\behaviors\\TimestampBehavior",
      "attributes": {
        "created_at": "timestamp_create",
        "updated_at": "timestamp_update"
      }
    }
  ]
}
```

Behaviors add extra functionality to components.

## Finding Components by Type

### All Database Connections
Look for components with `class` containing `yii\db\Connection`

### All Cache Components
Look for components with `class` containing `yii\caching`

### All Custom Components
Look for components with `class` containing `app\`

## Debugging Component Issues

### Component Not Working

1. Check if it's registered:
```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

2. If not in list:
   - Check application configuration
   - Verify component ID in `components` array
   - Check for configuration syntax errors

3. If in list but not working:
   - Check configuration values
   - Verify class name is correct
   - Check if dependencies are met

### Component Configuration Wrong

1. Get current config:
```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

2. Compare with expected values
3. Check logs for configuration errors
4. Verify environment variables are set

## Multiple Databases

If you have multiple database connections:

```json
{
  "id": "db",
  "class": "yii\\db\\Connection"
},
{
  "id": "replica",
  "class": "yii\\db\\Connection"
}
```

Access them:
- Primary: `Yii::$app->db`
- Replica: `Yii::$app->replica`

## Combining with Other Tools

### Find What Uses a Component

1. Get all components:
```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

2. Check logs for component access:
```json
{
  "name": "log_inspector",
  "arguments": {
    "search": "component_name"
  }
}
```

3. Check routes that might use it:
```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

### Understand Application Flow

1. See what components are available
2. Check routes to understand entry points
3. Use logs to trace actual usage

## Best Practices

1. **Review components regularly** - Understand what's configured
2. **Check singleton status** - Performance implications
3. **Verify dependencies** - Components may depend on others
4. **Monitor configuration** - Changes after deployments
5. **Check for unused components** - Clean up unnecessary configurations

## Next Steps

- Use [Database Inspection](database-inspection.md) to check database component
- Use [Routes](finding-routes.md) to see what uses these components
- Use [Logs](debugging-with-logs.md) to trace component usage
