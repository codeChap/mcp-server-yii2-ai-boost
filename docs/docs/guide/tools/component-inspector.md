# Component Inspector Tool

Introspect registered application components.

## Overview

The Component Inspector tool provides detailed information about all registered components in your Yii2 application.

## Parameters

All parameters are optional. No required parameters.

## Example Requests

### List All Components

```json
{
  "name": "component_inspector",
  "arguments": {}
}
```

## Example Response

```json
{
  "components": {
    "db": {
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
    },
    "cache": {
      "id": "cache",
      "class": "yii\\caching\\FileCache",
      "config": {
        "cachePath": "@runtime/cache"
      },
      "singleton": true,
      "behaviors": []
    },
    "log": {
      "id": "log",
      "class": "yii\\log\\Dispatcher",
      "config": {
        "traceLevel": 3
      },
      "singleton": true,
      "behaviors": []
    },
    "request": {
      "id": "request",
      "class": "yii\\web\\Request",
      "config": {},
      "singleton": true,
      "behaviors": []
    }
  }
}
```

## Response Structure

### components
Object containing all registered components where each key is the component ID.

For each component:
- `id` - Component identifier
- `class` - Component class name
- `config` - Configuration array (sensitive data redacted)
- `singleton` - Whether component is a singleton
- `behaviors` - Attached behaviors

## Use Cases

- Find all registered components
- Understand component configuration
- Identify singleton vs new instance components
- Check attached behaviors
- Verify component class names
