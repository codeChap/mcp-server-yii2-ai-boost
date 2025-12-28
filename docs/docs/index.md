# Yii2 AI Boost

Welcome to the official documentation for **Yii2 AI Boost**, a Model Context Protocol (MCP) server that provides AI assistants with comprehensive tools and guidelines for Yii2 application development.

## What is Yii2 AI Boost?

Yii2 AI Boost integrates with your Yii2 application to give AI assistants (like Claude) deep contextual understanding of your codebase. It provides:

- **6 Core MCP Tools** - Database inspection, config access, route analysis, component introspection, logging, and more
- **Unified Logging Access** - Read logs from files, databases, or current request memory
- **Framework Guidelines** - Comprehensive Yii2 best practices and patterns
- **IDE Integration** - Auto-configures for Claude Code, VS Code, PhpStorm, and Cursor
- **Interactive Installation** - Wizard-based setup with environment detection

## Quick Start

### Installation

```bash
cd /path/to/yii2/application
composer require codechap/yii2-ai-boost --dev
php yii boost/install
```

### Verify Installation

```bash
php yii boost/info
```

This will show you:
- Package version and configuration
- List of available MCP tools
- Status of guidelines and configuration files

## Documentation Sections

### [Guide](guide/index.md)
The comprehensive handbook for using Yii2 AI Boost. Learn how to install, configure, and use each of the 6 core tools with detailed examples.

### [Cookbook](cookbook/index.md)
Practical recipes and how-to guides for common debugging and analysis tasks. Real-world examples of solving problems with Yii2 AI Boost.

### [Internals](internals/index.md)
Technical documentation for extending Yii2 AI Boost. Learn the architecture, how to add new tools and readers, and how to contribute.

## Key Features

### Multi-Source Logging
The Log Inspector tool provides unified access to logs from multiple sources:

- **In-Memory Logs** - Current request logs with full stack traces
- **File Logs** - Text-based logs with automatic file rotation handling
- **Database Logs** - Persistent logs with fast queries and indexing

All three can be queried with a single tool call, with filtering by level, category, keywords, and time range.

### 6 Core Tools

1. **Application Info** - Yii2 version, environment, modules, extensions
2. **Database Schema** - Tables, columns, indexes, foreign keys, models
3. **Config Access** - Component, module, and parameter configurations
4. **Route Inspector** - URL rules, routes, REST endpoints
5. **Component Inspector** - Component listing, classes, configurations
6. **Log Inspector** - File, database, and in-memory logs with filtering

## System Requirements

- **PHP**: >= 7.4
- **Yii2**: >= 2.0.45

## License

BSD 3-Clause License. See LICENSE file for details.

## Support

- **Issues**: https://github.com/codeChap/mcp-server-yii2-ai-boost/issues
- **Repository**: https://github.com/codeChap/mcp-server-yii2-ai-boost
