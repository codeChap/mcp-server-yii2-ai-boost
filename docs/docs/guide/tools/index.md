# Tools

Yii2 AI Boost provides 6 core tools for deep introspection into your Yii2 application.

## Available Tools

### 1. [Application Info](application-info.md)
Get comprehensive information about your Yii2 application:
- Yii2 and PHP versions
- Application environment and debug status
- Installed modules
- Available extensions

### 2. [Database Schema](database-schema.md)
Inspect your database structure:
- List all tables with row counts
- View detailed table schemas
- Discover Active Record models
- View indexes and foreign keys

### 3. [Config Access](config-access.md)
Access application configuration safely:
- Component configurations
- Module configurations
- Application parameters (with sensitive data redaction)

### 4. [Route Inspector](route-inspector.md)
Analyze your application routes:
- URL rules and patterns
- Module routes with prefixes
- Controller and action mappings
- RESTful API endpoints

### 5. [Component Inspector](component-inspector.md)
Introspect application components:
- List all registered components
- View component classes and configurations
- Check singleton vs new instance behavior
- Inspect component properties

### 6. [Log Inspector](log-inspector.md)
Inspect application logs from all configured sources:
- Read logs from FileTarget (text files)
- Read logs from DbTarget (database table)
- Access in-memory logs (current request)
- Filter by log level, category, keywords, time range
- View stack traces (for in-memory logs)

## Core Architecture

All tools follow a consistent architecture based on the **BaseTool** class:

- **Automatic Sanitization** - Sensitive data is automatically redacted
- **Database Discovery** - Tools automatically detect configured connections
- **JSON Schema Validation** - Input parameters are validated
- **Error Handling** - Graceful error responses without exposing details

See [Architecture](architecture.md) for technical details.
