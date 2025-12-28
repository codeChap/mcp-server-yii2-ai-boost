# Yii2 AI Boost - MCP Server for Yii2 Applications

> **⚠️ Status: Active Development**
>
> This project is currently in **Phase 2** of development. Core tools are stable, but APIs and features may evolve. 
> We welcome feedback and contributions!

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-BSD--3--Clause-green)
![Yii2](https://img.shields.io/badge/Yii2-2.0.45-orange)

Yii2 AI Boost is a Model Context Protocol (MCP) server that provides AI assistants (like Claude) with comprehensive tools and guidelines for Yii2 application development.

## Features

- **6 Core MCP Tools** - Database inspection, config access, route analysis, component introspection, logging, and more
- **Framework Guidelines** - Comprehensive Yii2 best practices and patterns
- **IDE Integration** - Auto-configures MCP server in Claude Code, VS Code, PhpStorm, and Cursor
- **Ecosystem Support** - Guidelines for Gii, Debug module, RBAC, and REST APIs
- **Interactive Installation** - Wizard-based setup with environment detection

## Installation

### Step 1: Require the Package

```bash
cd /path/to/yii2/application
composer require codechap/yii2-ai-boost --dev
```

### Step 2: Run Installation Wizard

```bash
php yii boost/install
```

The wizard will:
- ✓ Detect your Yii2 environment
- ✓ Generate configuration files
- ✓ Create guidelines directory
- ✓ Auto-configure IDE integration (if available)

### Generated Files

After installation, you'll have:

- **`.mcp.json`** - MCP server configuration for IDEs
- **`boost.json`** - Package configuration and tool list
- **`CLAUDE.md`** - Application guidelines with framework patterns
- **`.ai/guidelines/`** - Framework and ecosystem guidelines

## Usage

### View Installation Status

```bash
php yii boost/info
```

Displays:
- Package version and configuration
- List of available MCP tools
- Status of guidelines and configuration files

### Start MCP Server (Manual Testing)

```bash
php yii boost/mcp
```

> ⚠️ **Note**: This command is typically invoked automatically by IDEs. You don't need to run it manually.

The server listens on STDIN for JSON-RPC requests and outputs responses to STDOUT.

### Update Guidelines

```bash
php yii boost/update
```

Updates downloaded guidelines to the latest versions.

## Available Tools

### 1. Application Info Tool
Get comprehensive information about your Yii2 application:
- Yii2 and PHP versions
- Application environment and debug status
- Installed modules and extensions

### 2. Database Schema Tool
Inspect your database structure:
- List all tables with row counts
- View detailed table schemas (columns, types, constraints)
- Discover Active Record models
- View indexes and foreign keys

### 3. Config Access Tool
Access application configuration safely:
- Component configurations
- Module configurations
- Application parameters (with sensitive data redaction)

### 4. Route Inspector Tool
Analyze your application routes:
- URL rules and patterns
- Module routes with prefixes
- Controller and action mappings
- RESTful API endpoints

### 5. Component Inspector Tool
Introspect application components:
- List all registered components
- View component classes and configurations
- Check singleton vs new instance behavior
- Inspect component properties

### 6. Log Inspector Tool
Inspect application logs from all configured sources:
- Read logs from FileTarget (text files)
- Read logs from DbTarget (database table)
- Access in-memory logs (current request)
- Filter by log level (error, warning, info, trace, profile)
- Filter by category with wildcard patterns
- Search logs by keywords
- Filter by time range
- View stack traces (for in-memory logs)

## Core Tools Architecture

All 6 core tools provide deep introspection into your Yii2 application. They follow a consistent architecture based on the **BaseTool** abstract class, which provides:

- **Automatic Sanitization**: Sensitive data (passwords, tokens, keys) is automatically redacted from all tool outputs
- **Database Discovery**: Tools automatically detect and access configured database connections
- **JSON Schema Validation**: Input parameters are validated against defined schemas
- **Error Handling**: Graceful error responses without exposing sensitive details

### How the Log Inspector Works

The Log Inspector is the most advanced tool, featuring a **multi-reader architecture** that handles different log storage methods transparently:

#### Three Reader Types

**1. InMemoryLogReader**
- **Source**: Current request logs in `Yii::getLogger()->messages`
- **Usage**: Real-time debugging, fastest access
- **Features**: Full stack traces, microsecond precision timestamps
- **Limitations**: Only current request, cleared on shutdown
- **Best For**: Immediate diagnostics during development

**2. FileLogReader**
- **Source**: FileTarget text logs (default: `@runtime/logs/app.log`)
- **Usage**: Historical logs on disk
- **Features**: Handles large files efficiently (tails 5MB+ files), auto-detects file rotation
- **Limitations**: No indexed search, text parsing required
- **Best For**: Reviewing logs from previous requests/sessions

**3. DbLogReader**
- **Source**: DbTarget database table (default: `{{%log}}`)
- **Usage**: Persistent, queryable logs
- **Features**: Fast indexed queries, precise time-range filtering, optimal for large volumes
- **Limitations**: Requires database table setup
- **Best For**: Production logging, log aggregation, performance analysis

## Tools Roadmap

| Phase | Tool | Status | Description |
|:-----:|------|--------|-------------|
| **1** | **application_info** | ✓ Complete | Yii2 version, environment, modules, extensions |
| **1** | **database_schema** | ✓ Complete | Tables, columns, indexes, models, foreign keys |
| **1** | **config_access** | ✓ Complete | Component, module, and parameter configurations |
| **1** | **route_inspector** | ✓ Complete | URL rules, routes, REST endpoints |
| **1** | **component_inspector** | ✓ Complete | Component listing, classes, configurations |
| **1** | **log_inspector** | ✓ Complete | File, database, and in-memory logs with filtering |
| 2 | model_inspector | 🔲 Planned | Active Record model analysis, properties, relations |
| 2 | validation_rules | 🔲 Planned | Model validation rules, error messages, constraints |
| 2 | migration_inspector | 🔲 Planned | List migrations, status, rollback history |
| 2 | behavior_inspector | 🔲 Planned | Attached behaviors, methods, event handlers |
| 2 | event_inspector | 🔲 Planned | Application events, listeners, handlers |
| 2 | database_query | 🔲 Planned | Safe read-only database queries (limited rows) |
| 2 | asset_manager | 🔲 Planned | Asset bundles, dependencies, registration status |
| 2 | widget_inspector | 🔲 Planned | Available widgets, usage, properties |
| 2 | security_audit | 🔲 Planned | Common security issues, CSRF, SQL injection risks |
| 2 | code_search | 🔲 Planned | Search codebase by patterns, class names, functions |
| 3 | fixture_inspector | 🔲 Future | Test fixtures, data generation, loading |
| 3 | rest_generator | 🔲 Future | Help generate REST API controllers/endpoints |
| 3 | performance_profiler | 🔲 Future | Query profiling, timing, bottleneck detection |
| 3 | dependency_analyzer | 🔲 Future | Composer dependencies, versions, conflicts |
| 3 | documentation_search | 🔲 Future | Search Yii2 official docs with context |
| 3 | cache_inspector | 🔲 Future | Cache components, performance metrics |
| 3 | environment_analyzer | 🔲 Future | PHP configuration, extensions, system info |

## MCP Protocol

Yii2 AI Boost implements the Model Context Protocol (MCP) v2025-11-25:

- **Transport**: STDIO (local) - reads from stdin, writes to stdout
- **Format**: JSON-RPC 2.0
- **Tools**: Expose functionality to AI assistants
- **Resources**: Provide static content (guidelines, configuration)

### Example JSON-RPC Request

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "application_info",
    "arguments": {
      "include": ["version", "environment", "modules"]
    }
  }
}
```

### Example Response

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "version": {
      "yii2_version": "2.0.45",
      "php_version": "8.1.2",
      "php_sapi": "cli"
    },
    "environment": {
      "yii_env": "dev",
      "yii_debug": true,
      "base_path": "/path/to/app",
      "runtime_path": "/path/to/app/runtime"
    },
    "modules": {
      "site": {
        "class": "app\\modules\\site\\Module",
        "basePath": "/path/to/app/modules/site"
      }
    }
  }
}
```

## Guidelines

The package includes comprehensive guidelines for Yii2 development:

### Core Framework Guidelines
- **File**: `.ai/guidelines/core/yii2-2.0.45.md`
- **Coverage**: Application structure, controllers, models, views, components, security, performance, console commands
- **Best Practices**: Common patterns and anti-patterns

### Ecosystem Guidelines (Coming Soon)
- Gii code generator patterns
- Debug module usage
- RBAC (Role-Based Access Control)
- RESTful API conventions

All guidelines are included in `CLAUDE.md` for easy reference.

## Configuration

### Manual Configuration

Edit `boost.json` to customize:

```json
{
  "version": "1.0.0",
  "yii2_version": "2.0.45",
  "tools": {
    "database_query": {
      "enabled": true,
      "readonly": true,
      "max_rows": 100
    }
  }
}
```

### IDE Configuration

The package auto-generates `.mcp.json` for IDE integration:

```json
{
  "mcpServers": {
    "yii2-boost": {
      "command": "php",
      "args": ["yii", "boost/mcp"],
      "cwd": "/path/to/app"
    }
  }
}
```

For Zed create or open your settings file in .zed/settings.json

```json
{
    "context_servers": {
      "yii2-ai-boost": {
        "enabled": true,
        "command": "php",
        "args" : [
            "yii", "boost/mcp"
        ]
      }
    }
}
```

## Troubleshooting

If the MCP server is not working as expected, check the log files:

- **Startup Log**: `@runtime/logs/mcp-startup.log` (Initialization status)
- **Error Log**: `@runtime/logs/mcp-errors.log` (PHP errors and exceptions)
- **Request Log**: `@runtime/logs/mcp-requests.log` (JSON-RPC traffic)
- **Transport Log**: `sys_get_temp_dir() . '/mcp-server/mcp-transport.log'` (Low-level transport debug)

Ensure that your PHP environment meets the requirements and that the `yii` command is executable.

### Running Tests

```bash
composer test              # Run unit tests
composer test:coverage     # Generate coverage report
composer cs-check          # Check code style (PSR-12)
composer analyze           # Run static analysis (PHPStan level 8)
```

## Requirements

- **PHP**: >= 7.4
- **Yii2**: >= 2.0.45

## Development Timeline

- **Phase 1 (MVP)**: 5 core tools, installation wizard, core guidelines ✓
- **Phase 2**: 10 additional tools, ecosystem guidelines (in progress)
- **Phase 3**: Documentation search, optimization, production release

## License

BSD 3-Clause License. See LICENSE file for details.

## Support

- **Issues**: https://github.com/codechap/yii2-ai-boost/issues
- **Documentation**: See `.ai/guidelines/` directory

---

**Yii2 AI Boost** - Making Yii2 development smarter with AI assistants.
