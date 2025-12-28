# Config Access Tool

Access application configuration safely with automatic sensitive data redaction.

## Overview

The Config Access tool provides safe access to your Yii2 application configuration including components, modules, and parameters. Sensitive data like passwords and tokens are automatically redacted.

## Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | `all` | Type of config to access: `component`, `module`, `param`, `all` |
| `name` | string | null | Specific component/module/parameter name |

## Example Requests

### Get All Configuration

```json
{
  "name": "config_access",
  "arguments": {
    "type": "all"
  }
}
```

### Get Component Configuration

```json
{
  "name": "config_access",
  "arguments": {
    "type": "component",
    "name": "db"
  }
}
```

### Get Module Configuration

```json
{
  "name": "config_access",
  "arguments": {
    "type": "module",
    "name": "admin"
  }
}
```

### Get Application Parameters

```json
{
  "name": "config_access",
  "arguments": {
    "type": "param"
  }
}
```

## Example Response

```json
{
  "components": {
    "db": {
      "class": "yii\\db\\Connection",
      "dsn": "mysql:host=localhost;dbname=myapp",
      "username": "***REDACTED***",
      "password": "***REDACTED***",
      "charset": "utf8"
    },
    "cache": {
      "class": "yii\\caching\\FileCache"
    }
  },
  "modules": {
    "admin": {
      "class": "app\\modules\\admin\\Module",
      "basePath": "/path/to/app/modules/admin"
    }
  },
  "params": {
    "adminEmail": "admin@example.com",
    "bcc": "bcc@example.com"
  }
}
```

## Automatic Sanitization

The following keys are automatically redacted:
- `password`
- `secret`
- `key`
- `token`
- `api_key`
- `private_key`
- `auth_key`
- `access_token`
- `refresh_token`
- `client_secret`

## Use Cases

- Understand component configuration
- Find module settings
- Check application parameters
- Verify configuration values safely
