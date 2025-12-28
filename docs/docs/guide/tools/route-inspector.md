# Route Inspector Tool

Analyze your application routes and URL rules.

## Overview

The Route Inspector tool provides complete analysis of your application routing configuration including URL rules, REST endpoints, and route patterns.

## Parameters

All parameters are optional. No required parameters.

## Example Requests

### Get All Routes

```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

## Example Response

```json
{
  "rules": [
    {
      "pattern": "site/error",
      "route": "site/error",
      "methods": []
    },
    {
      "pattern": "site/login",
      "route": "site/login",
      "methods": []
    },
    {
      "pattern": "site/logout",
      "route": "site/logout",
      "methods": []
    }
  ],
  "rest_routes": [
    {
      "pattern": "api/user/<id:\\d+>",
      "route": "api/user/view",
      "method": "GET",
      "action": "view"
    },
    {
      "pattern": "api/user",
      "route": "api/user/create",
      "method": "POST",
      "action": "create"
    }
  ],
  "modules": {
    "admin": {
      "prefix": "admin",
      "routes": [
        {
          "pattern": "dashboard",
          "route": "admin/dashboard/index",
          "methods": []
        }
      ]
    }
  }
}
```

## Response Structure

### rules
Array of configured URL rules with pattern and route mapping.

### rest_routes
RESTful API routes with HTTP method information.

### modules
Module-specific routes grouped by module name with URL prefix.

## Use Cases

- Map URL patterns to controllers/actions
- Find REST API endpoints
- Understand routing structure
- Debug route conflicts
- Discover available routes
