# Application Info Tool

Get comprehensive information about your Yii2 application.

## Overview

The Application Info tool provides metadata about your Yii2 installation including versions, environment, modules, and extensions.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `include` | array | `["version", "environment", "modules", "extensions"]` | What info to include |

### Include Options

- `version` - PHP and Yii2 versions
- `environment` - Environment type and paths
- `modules` - Installed modules
- `extensions` - Installed Yii extensions
- `all` - Include everything

## Example Requests

### Get All Information

```json
{
  "name": "application_info",
  "arguments": {
    "include": ["all"]
  }
}
```

### Get Only Version Info

```json
{
  "name": "application_info",
  "arguments": {
    "include": ["version"]
  }
}
```

### Get Environment and Modules

```json
{
  "name": "application_info",
  "arguments": {
    "include": ["environment", "modules"]
  }
}
```

## Example Response

```json
{
  "version": {
    "yii2_version": "2.0.45",
    "php_version": "8.1.2",
    "php_sapi": "cli"
  },
  "environment": {
    "yii_env": "dev",
    "yii_debug": true,
    "base_path": "/path/to/app",
    "runtime_path": "/path/to/app/runtime",
    "web_path": "/path/to/app/web"
  },
  "modules": {
    "site": {
      "class": "app\\modules\\site\\Module",
      "basePath": "/path/to/app/modules/site"
    }
  },
  "extensions": {
    "yiisoft/yii2-debug": {
      "version": "2.1.15",
      "description": "The Yii framework debug toolbar"
    }
  }
}
```

## Use Cases

- Verify PHP and Yii2 versions
- Check if you're in development or production
- Discover available modules
- Identify installed ecosystem packages
