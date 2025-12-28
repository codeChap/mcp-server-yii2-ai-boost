# Internals

Technical documentation for developers contributing to or extending Yii2 AI Boost.

## Contents

### [Architecture](architecture.md)
Deep dive into the system architecture, design patterns, and how components interact.

### [Adding Tools](adding-tools.md)
Learn how to create new tools and integrate them into the MCP server.

### [Adding Readers](adding-readers.md)
Extend the Log Inspector with custom log readers for new storage backends.

### [Contributing](contributing.md)
Guidelines for contributing code, documentation, and bug reports.

## Quick Facts

- **Language**: PHP 7.4+
- **Framework**: Yii2 2.0.45+
- **Protocol**: MCP (Model Context Protocol) v2025-11-25
- **Transport**: STDIO (JSON-RPC 2.0)
- **License**: BSD 3-Clause

## Key Design Principles

1. **Security First** - Automatic data sanitization, no shell execution
2. **Stateless** - Tools have no internal state between calls
3. **Extensible** - Plugin architecture for tools and readers
4. **Graceful Degradation** - Missing features don't break the system
5. **Zero Configuration** - Works out of the box after installation

## Project Structure

```
src/
├── Mcp/
│   ├── Server.php              # Main orchestrator
│   ├── Tools/                  # Tool implementations
│   │   ├── Base/
│   │   │   └── BaseTool.php
│   │   ├── ApplicationInfoTool.php
│   │   ├── DatabaseSchemaTool.php
│   │   ├── ConfigAccessTool.php
│   │   ├── RouteInspectorTool.php
│   │   ├── ComponentInspectorTool.php
│   │   ├── LogInspectorTool.php
│   │   └── Readers/
│   │       ├── LogReaderInterface.php
│   │       ├── InMemoryLogReader.php
│   │       ├── FileLogReader.php
│   │       └── DbLogReader.php
│   ├── Resources/              # Static content providers
│   │   ├── BaseResource.php
│   │   ├── GuidelinesResource.php
│   │   └── BoostConfigResource.php
│   └── Transports/             # Communication layers
│       ├── TransportInterface.php
│       └── StdioTransport.php
├── Commands/                   # Console commands
├── Bootstrap.php               # Yii2 integration
└── ...
```

## Key Concepts

### BaseTool
Abstract base class that all tools extend. Provides:
- Automatic sanitization of sensitive data
- Database connection discovery
- JSON schema validation
- Error handling

### MCP Server
Central orchestrator that:
- Manages tool and resource registry
- Routes JSON-RPC requests to handlers
- Handles protocol version negotiation
- Manages logging and error responses

### Readers
Pluggable components for the Log Inspector:
- **InMemoryLogReader** - Current request logs
- **FileLogReader** - Text file logs
- **DbLogReader** - Database logs

### Yii2 Integration
The `Bootstrap` class integrates Yii2 without requiring configuration.

## Common Tasks

### Understanding Code Flow

1. User calls tool via IDE
2. IDE sends JSON-RPC request to STDIO
3. `StdioTransport::listen()` reads from STDIN
4. `Server::handleRequest()` parses JSON
5. `Server::dispatch()` routes to tool handler
6. Tool extends `BaseTool` and runs `execute()`
7. Result passed through `sanitize()`
8. Response formatted as JSON-RPC 2.0
9. Response written to STDOUT

### Adding Debug Output

Use `error_log()` - it goes to STDERR, not affecting STDOUT JSON:

```php
error_log("Debug message");
```

Never use `echo`, `var_dump`, or `print_r` - corrupts STDOUT.

### Testing a Tool

Use manual request via STDIN:

```bash
php yii boost/mcp <<'EOF'
{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"application_info","arguments":{}}}
EOF
```

## Next Steps

- Read [Architecture](architecture.md) for system design
- Learn [Adding Tools](adding-tools.md) to extend functionality
- Check [Contributing](contributing.md) guidelines
- Review [Adding Readers](adding-readers.md) for Log Inspector extension
