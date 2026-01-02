# Yii2 AI Boost - MCP Server for Yii2 Applications

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-BSD--3--Clause-green)
![Yii2](https://img.shields.io/badge/Yii2-2.0.45-orange)

Yii2 AI Boost is a Model Context Protocol (MCP) server that provides AI assistants (like Claude Code) with comprehensive tools and guidelines for faster Yii2 application development.

---

## Features

- **8 Core MCP Tools** - Database inspection and queries, config access, route analysis, component introspection, logging, and guideline search
- **On-Demand Guidelines** - AI searches 36KB of Yii2 best practices only when needed (zero context cost until requested)
- **Framework Guidelines** - Comprehensive Yii2 patterns covering controllers, models, migrations, caching, auth, and more
- **IDE Integration** - Works with Claude Code, Cursor, Zed, and other MCP-compatible editors

---

## Quick Start

For experienced developers:

```bash
# 1. Install package
composer require codechap/yii2-ai-boost:^1.0 --dev

# 2. Run installation
php yii boost/install

# 3. (Optional) Sync guidelines to your editor (Cursor/Zed)
php yii boost/sync-rules
```

That's it! Claude Code and other AI tools now have access to your application context.

---

## Installation

### **Step 1**: Require the Package

```bash
cd /path/to/yii2/application
composer require codechap/yii2-ai-boost:^1.0 --dev
```

### **Step 2**: Run Installation Wizard

```bash
php yii boost/install
```

The installation runs automatically and:
- Detects your Yii2 environment
- Generates configuration files (`.mcp.json`, `boost.json`)
- Copies framework guidelines to `.ai/guidelines/`

If installation fails, please [open an issue](https://github.com/codechap/yii2-ai-boost/issues) or reach out on [X](https://x.com/codechap).

### **Step 3**: Connect Claude Code and or your IDE

__Claude Code integration__

After running `php yii boost/install`, a `.mcp.json` file will be generated in your project root. Claude Code will automatically detect and use this configuration to connect to the MCP server.

__Codex CLI Configuration__
@todo

__Gemini CLI Configuration__
@todo

__Zed Configuration__

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

### Generated Files

After installation, you'll have:

- **`.mcp.json`** - MCP server configuration for Claude Code
- **`boost.json`** - Package configuration and tool list
- **`.ai/guidelines/`** - Framework and ecosystem guidelines (Markdown)
- **`.cursor/rules/yii2-boost.mdc`** - (Optional) Generated rules for Cursor
- **`.rules`** - (Optional) Generated rules for Zed

---

## Usage

### View Yii2 information

```bash
php yii boost/info
```

Displays:
- Package version and configuration
- List of available MCP tools
- Status of guidelines and configuration files

### Sync Editor Rules

```bash
php yii boost/sync-rules
```

Automatically generates:
- **Cursor**: `.cursor/rules/yii2-boost.mdc`
- **Zed**: `.rules` (in project root)

These files contain the core Yii2 guidelines and structural references, giving your AI editor "X-Ray vision" into Yii2 best practices without manual prompting.

### Start MCP Server (Manual Testing)

```bash
php yii boost/mcp
```

> ⚠️ **Note**: This command is invoked automatically by Cluade Code or your editor. You don't need to run it manually.

The server listens on STDIN for JSON-RPC requests and outputs responses to STDOUT.

### Update Guidelines

```bash
php yii boost/update
```

Copies updated guidelines to the relevant folders.

---

## Guidelines & Editor Integration

Yii2 AI Boost comes with a rich library of "Context Anchors" in `.ai/guidelines/`. These are Markdown files that define exact structures for Yii2 components (Controllers, Models, Migrations, etc.), preventing AI hallucinations.

### 1. Active Search (MCP Tool)
The MCP server includes a `search_guidelines` tool. AI agents (like Claude or Gemini) can use this to "look up" how to do things in Yii2.
*   *User:* "How do I create a migration?"
*   *AI:* Calls `search_guidelines(query="migration")` -> Reads `database/yii-migration.md` -> Writes perfect code.

### 2. Passive Context (Editor Rules)
Run `php yii boost/sync-rules` to bake these guidelines directly into your editor's context.
*   **Cursor**: Creates a `.mdc` rule file.
*   **Zed**: Creates a `.rules` file.

This means when you open a file in Zed or Cursor, the AI *already knows* it should use `yii\web\Controller` and not `Illuminate\Routing\Controller`.

---

## What is MCP?

The **Model Context Protocol (MCP)** is an open standard that enables AI assistants to interact with tools and data sources. MCP allows Claude Code and other AI tools to securely access your application's context—database schemas, configuration, routes, and logs—without exposing sensitive data.

Yii2 AI Boost implements MCP v2025-11-25 using JSON-RPC 2.0 over STDIO transport. This means Claude Code communicates with your application through standard input/output, with no need for network configuration.

[Learn more about MCP](https://modelcontextprotocol.io/)

---

## Available Tools

### 1. `application_info` - Application Info
Get comprehensive information about your Yii2 application:
- Yii2 and PHP versions
- Application environment and debug status
- Installed modules and extensions

### 2. `database_schema` - Database Schema
Inspect your database structure:
- List all tables with row counts
- View detailed table schemas (columns, types, constraints)
- Discover Active Record models
- View indexes and foreign keys

### 3. `database_query` - Database Query
Execute SQL queries against your database:
- Run SELECT queries with automatic row limiting
- Support for bound parameters
- Returns execution time and row count
- Works with any configured database connection

### 4. `config_access` - Config Access
Access application configuration safely:
- Component configurations
- Module configurations
- Application parameters (with sensitive data redaction)

### 5. `route_inspector` - Route Inspector
Analyze your application routes:
- URL rules and patterns
- Module routes with prefixes
- Controller and action mappings
- RESTful API endpoints

### 6. `component_inspector` - Component Inspector
Introspect application components:
- List all registered components
- View component classes and configurations
- Check singleton vs new instance behavior
- Inspect component properties

### 7. `log_inspector` - Log Inspector
Inspect application logs from all configured sources:
- Read logs from FileTarget (text files)
- Read logs from DbTarget (database table)
- Access in-memory logs (current request)
- Filter by log level (error, warning, info, trace, profile)
- Filter by category with wildcard patterns
- Search logs by keywords
- Filter by time range
- View stack traces (for in-memory logs)

### 8. `search_guidelines` - Guideline Search
Search the local Yii2 AI Guidelines database:
- Find best practices for Controllers, Models, Migrations, etc.
- Retrieve structural reference code to prevent hallucinations
- Filter by category (e.g., 'database', 'security', 'views')
- Returns full Markdown content of the most relevant guides

## Core Tools Architecture

All 8 core tools provide deep introspection into your Yii2 application. They follow a consistent architecture based on the **BaseTool** abstract class, which provides:

- **Automatic Sanitization**: Sensitive data (passwords, tokens, keys) is automatically redacted from all tool outputs
- **Database Discovery**: Tools automatically detect and access configured database connections
- **JSON Schema Validation**: Input parameters are validated against defined schemas
- **Error Handling**: Graceful error responses without exposing sensitive details

### How the Log Inspector Works

The Log Inspector features a **multi-reader architecture** supporting three log storage methods:

| Reader | Source | Best For | Features |
|--------|--------|----------|----------|
| **InMemoryLogReader** | Current request logs (`Yii::getLogger()->messages`) | Real-time debugging during development | Full stack traces, microsecond timestamps |
| **FileLogReader** | FileTarget text logs (`@runtime/logs/app.log`) | Reviewing logs from previous requests/sessions | Efficient file handling (5MB+ files), auto-detects rotation |
| **DbLogReader** | DbTarget database table (`{{%log}}`) | Production logging & log aggregation | Fast indexed queries, precise time-range filtering |

---

## Tools Roadmap

| Phase | Tool | Status | Description |
|:-----:|------|--------|-------------|
| **1** | **application_info** | ✓ Complete | Yii2 version, environment, modules, extensions |
| **1** | **database_schema** | ✓ Complete | Tables, columns, indexes, models, foreign keys |
| **1** | **config_access** | ✓ Complete | Component, module, and parameter configurations |
| **1** | **route_inspector** | ✓ Complete | URL rules, routes, REST endpoints |
| **1** | **component_inspector** | ✓ Complete | Component listing, classes, configurations |
| **1** | **log_inspector** | ✓ Complete | File, database, and in-memory logs with filtering |
| **1** | **search_guidelines** | ✓ Complete | On-demand Yii2 guidelines search with categories |
| **1** | **database_query** | ✓ Complete | Execute database queries (limited rows) |
| 2 | model_inspector | 🔲 Planned | Active Record model analysis, properties, relations |
| 2 | validation_rules | 🔲 Planned | Model validation rules, error messages, constraints |
| 3 | migration_inspector | 🔲 Planned | List migrations, status, rollback history |
| 3 | asset_manager | 🔲 Planned | Asset bundles, dependencies, registration status |
| 3 | widget_inspector | 🔲 Planned | Available widgets, usage, properties |
| 3 | performance_profiler | 🔲 Planned | Query profiling, timing, bottleneck detection |
| 4 | behavior_inspector | 🔲 Future | Attached behaviors, methods, event handlers |
| 4 | event_inspector | 🔲 Future | Application events, listeners, handlers |
| 4 | cache_inspector | 🔲 Future | Cache components, performance metrics |
| 4 | environment_analyzer | 🔲 Future | PHP configuration, extensions, system info |
| 4 | semantic_search | 🔲 Future | Enhanced guidelines search with semantic matching |

---

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

**Response Structure**:
- `jsonrpc`: Always `"2.0"` per JSON-RPC spec
- `id`: Echoes the request ID for request/response matching
- `result`: The actual tool output (sensitive data automatically redacted)

**Error Responses** use `error` instead of `result`:
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32603,
    "message": "Internal error",
    "data": "Error details here"
  }
}
```

---

## Guidelines

The package downloads comprehensive Yii2 development guidelines to `.ai/guidelines/core/yii2-2.0.45.md`. These cover application structure, controllers, models, views, components, security, performance, and console commands.

### Including Guidelines in Your AI Workflow

To use these guidelines with Claude Code or other AI tools, add the following lines to your project's `CLAUDE.md` file:

```markdown
@include .ai/guidelines/core/yii2-2.0.45.md
```

Your AI assistant can also search additional guidelines on-demand via the `search_guidelines` MCP tool (database, cache, auth, validation, etc.).

This ensures AI assistants have access to framework best practices and patterns when working in your project.

---

## Troubleshooting

### Getting Help

If you encounter issues:
1. Check the log files listed below for error details
2. Open an issue: https://github.com/codechap/yii2-ai-boost/issues
3. Reach out on X: https://x.com/codechap

### Log Files

When debugging, check these log files:

- **Startup Log**: `@runtime/logs/mcp-startup.log` — Server initialization and tool registration
- **Error Log**: `@runtime/logs/mcp-errors.log` — PHP errors and exceptions
- **Request Log**: `@runtime/logs/mcp-requests.log` — JSON-RPC requests and responses
- **Transport Log**: `/tmp/mcp-server/mcp-transport.log` — Low-level STDIO communication

### FAQ

_This section will be expanded as common questions arise. For now, please reach out with issues or questions._

---

## Requirements

| Component | Version | Status |
|-----------|---------|--------|
| **PHP** | 7.4, 8.0, 8.1, 8.2, 8.3 | ✓ Tested |
| **Yii2** | 2.0.45+ | ✓ Compatible |

**Why PHP 7.4?** While PHP 7.4 is EOL, Yii2 itself still supports it. As a Yii2 extension, we maintain the same baseline to ensure developers on older Yii2 installations aren't locked out. If your Yii2 app runs, this tool should too.

**Why no caching?** All introspection data is fetched fresh on every request. This is intentional - as a development tool, you need to see the current state of your application, not stale cached data. When you change a route, schema, or component, the tools should reflect that immediately.

**Note**: PHP 8.4 support pending. Report any compatibility issues on [GitHub](https://github.com/codechap/yii2-ai-boost/issues).

## Development Timeline

| Phase | Goal | Status | Tools |
|-------|------|--------|-------|
| **1** | Core MVP | ✓ Complete | 8 tools + guidelines + installer |
| **2** | Extended Tools | Planned | +10 tools (model inspector, validation, queries) |
| **3** | Advanced Features | Planned | Performance profiling, behavior/event inspection |

Track progress and contribute at [GitHub](https://github.com/codechap/yii2-ai-boost).

## License

BSD 3-Clause License. See LICENSE file for details.

## Contributing

Contributions are welcome! Here's how to get started:

1. **Fork** the repository
2. **Clone** your fork and create a branch (`git checkout -b feature/my-feature`)
3. **Install** dependencies (`composer install`)
4. **Make** your changes
5. **Test** your changes (`composer test`)
6. **Check** code style (`composer cs-check`) and fix if needed (`composer cs-fix`)
7. **Run** static analysis (`composer analyze`)
8. **Commit** with a clear message and **push** to your fork
9. **Open** a Pull Request against `master`

### Guidelines

- Follow PSR-12 code style
- Add tests for new functionality where practical
- Keep changes focused - one feature/fix per PR
- Update documentation if adding new tools or changing behavior

### Areas Where Help is Appreciated

- Additional test coverage (especially integration tests)
- New tools from the [roadmap](#tools-roadmap)
- Documentation improvements
- Bug reports with reproduction steps

## Support & Feedback

- **Bug Reports & Feature Requests**: [GitHub Issues](https://github.com/codechap/yii2-ai-boost/issues)
- **Direct Contact**: [@codechap on X](https://x.com/codechap)

---

**Yii2 AI Boost** - Making Yii2 development smarter and faster with AI assistants.
