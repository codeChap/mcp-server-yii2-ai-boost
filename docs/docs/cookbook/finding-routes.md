# Finding Routes

Discover and analyze your application's routing configuration.

## Overview

The Route Inspector tool helps you understand how URLs map to controllers and actions, and find available endpoints.

## Getting Started

### List All Routes

See every route in your application:

```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

This returns three sections:
- **rules** - Standard URL rules
- **rest_routes** - RESTful API routes
- **modules** - Routes within modules

## Understanding Route Structure

### Standard Route

```json
{
  "pattern": "site/error",
  "route": "site/error",
  "methods": []
}
```

- `pattern` - URL pattern (what users see)
- `route` - Controller/action (internal route)
- `methods` - HTTP methods (empty for web routing)

### REST Route

```json
{
  "pattern": "api/user/<id:\\d+>",
  "route": "api/user/view",
  "method": "GET",
  "action": "view"
}
```

- `method` - HTTP verb (GET, POST, PUT, DELETE)
- `action` - RESTful action (view, create, update, delete)
- `pattern` - URL with parameter placeholders

## Common Scenarios

### Scenario: "What routes exist?"

```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

Check the response for:
- Total number of routes
- RESTful API routes (if any)
- Module-specific routes
- Pattern naming conventions

### Scenario: "What's the URL for X action?"

Look through the routes for your controller/action:

```
If you want to reach SiteController::actionError
Look for: "route": "site/error"
Check what URL patterns map to it
```

### Scenario: "Does this API endpoint exist?"

Search the `rest_routes` section:

```
Looking for GET /api/user/42?
Find: pattern "api/user/<id:\d+>" with method "GET"
Yes, it exists - action is "view"
```

### Scenario: "What module routes are available?"

Check the `modules` section:

```
If module is "admin" with prefix "admin"
And route is "dashboard/index"
Full URL would be: /admin/dashboard
```

### Scenario: "Find all API endpoints"

Look through `rest_routes`:

```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

Count entries with different methods:
- GET requests = read operations
- POST requests = create operations
- PUT requests = update operations
- DELETE requests = delete operations

## Route Patterns Explained

### Simple Pattern
```
site/login
→ URL: /site/login
→ Controller: SiteController
→ Action: actionLogin
```

### Pattern with Parameter
```
post/<id:\d+>
→ URL: /post/42
→ Captures: id = 42
→ Constraint: id must be digits
```

### Pattern with Multiple Parameters
```
user/<username>/post/<id:\d+>
→ URL: /user/john/post/5
→ Captures: username = john, id = 5
```

### Module Route
```
admin (prefix "admin")
dashboard/index
→ URL: /admin/dashboard
```

## Debugging Routes

### Route Not Working

Use Route Inspector to verify:

1. Check if route exists:
```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

2. If not found:
   - Check rule pattern and spelling
   - Verify controller/action names
   - Check module prefix

3. If found but not working:
   - Verify URL pattern matches what you're accessing
   - Check for conflicting rules
   - Review parameter constraints

### Parameter Issues

```
If pattern is: post/<id:\d+>
But you visit: /post/abc
Error! "abc" doesn't match \d+ (digits only)
```

Solution: Check the constraint in the pattern.

## API Documentation

### Export API Routes

Collect all REST API routes:

```json
{
  "name": "route_inspector",
  "arguments": {}
}
```

Then extract from `rest_routes`:
- Group by resource (user, post, etc)
- List HTTP method and URL
- Document action names

### Create API Reference

From the response, you can generate:

```
GET    /api/user          - List users
GET    /api/user/<id>     - Get user
POST   /api/user          - Create user
PUT    /api/user/<id>     - Update user
DELETE /api/user/<id>     - Delete user
```

## Combining with Other Tools

### Find Handler for URL

1. Use Route Inspector to see what controller/action handles the URL
2. Use Config Access to see if that controller is configured
3. Use Component Inspector to check related components

### Understand API Flow

1. Find API routes with Route Inspector
2. Check database schema for related tables
3. Use Log Inspector to trace actual requests

## Module Routes Deep Dive

Module routes appear in the `modules` section:

```json
{
  "admin": {
    "prefix": "admin",
    "routes": [
      {
        "pattern": "dashboard",
        "route": "admin/dashboard/index"
      }
    ]
  }
}
```

This means:
- Module name: `admin`
- URL prefix: `/admin/`
- Route: `/admin/dashboard`
- Maps to: AdminModule → DashboardController → actionIndex

## Performance Insights

### Too Many Routes?

If you have hundreds of routes, check for:
- Overlapping patterns
- Redundant rules
- Rules that should be consolidated

### Route Resolution Order

Rules are checked in order - the first matching rule wins.

Look for potential conflicts:
- More specific patterns should come first
- Catch-all patterns should be last

## Next Steps

- Use [Database Inspection](database-inspection.md) to understand data models
- Use [Component Analysis](component-analysis.md) to understand controllers
- Use [Logs](debugging-with-logs.md) to trace actual requests
