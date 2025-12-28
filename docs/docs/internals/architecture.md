# Architecture

Deep dive into Yii2 AI Boost system design and architecture.

## System Overview

```
┌─────────────────────────────────────────────────────────┐
│ IDE (Claude Code, VS Code, PhpStorm, etc)              │
└────────────────────┬────────────────────────────────────┘
                     │ JSON-RPC 2.0 Request
                     ▼
┌─────────────────────────────────────────────────────────┐
│ StdioTransport                                          │
│ └─ Reads STDIN, writes STDOUT                          │
└────────────────────┬────────────────────────────────────┘
                     │ Raw JSON String
                     ▼
┌─────────────────────────────────────────────────────────┐
│ Server (Main Orchestrator)                             │
│ ├─ handleRequest() - Parse & validate                  │
│ ├─ dispatch() - Route to handler                       │
│ └─ Error handling                                      │
└────────────────────┬────────────────────────────────────┘
                     │
           ┌─────────┴─────────┐
           │                   │
           ▼                   ▼
    ┌─────────────┐     ┌─────────────┐
    │ Tools       │     │ Resources   │
    │ - Call      │     │ - Read      │
    │ - Execute   │     │ - Serialize │
    └──────┬──────┘     └─────────────┘
           │
           ▼
    ┌──────────────────┐
    │ Yii2 Application │
    │ - Database       │
    │ - Config         │
    │ - Routes         │
    │ - Components     │
    └──────────────────┘
```

## Component Architecture

### Server (Core Orchestrator)

**File**: `src/Mcp/Server.php`

Responsibilities:
- Listens for JSON-RPC requests via transport
- Parses and validates JSON
- Routes method calls to handlers
- Manages tool/resource registry
- Error handling and logging
- Response formatting

Key methods:
- `initialize()` - Protocol negotiation
- `dispatch()` - Route method to handler
- `handleRequest()` - Main request processor
- `callTool()` - Execute tool with arguments
- `readResource()` - Read and serialize resource

### BaseTool (Tool Foundation)

**File**: `src/Mcp/Tools/Base/BaseTool.php`

All 6 tools extend this abstract class.

Provides:
- `sanitize()` - Recursive data sanitization
- `getDbConnections()` - Discover database connections
- `getDbDriver()` - Extract driver from DSN
- Abstract methods for tool implementation

All tools must implement:
- `getName()` - Tool identifier
- `getDescription()` - Human-readable description
- `getInputSchema()` - JSON Schema for parameters
- `execute(array $arguments)` - Tool logic

### Tools (6 Core Tools)

| Tool | File | Purpose |
|------|------|---------|
| ApplicationInfo | ApplicationInfoTool.php | App metadata |
| DatabaseSchema | DatabaseSchemaTool.php | Database introspection |
| ConfigAccess | ConfigAccessTool.php | Configuration access |
| RouteInspector | RouteInspectorTool.php | Route analysis |
| ComponentInspector | ComponentInspectorTool.php | Component introspection |
| LogInspector | LogInspectorTool.php | Log access & filtering |

### Log Inspector Readers

**Directory**: `src/Mcp/Tools/Readers/`

Multi-reader architecture:

```
LogInspectorTool
├─ Discovers available readers
├─ Queries each reader
├─ Merges results
└─ Returns aggregated summary
```

Three readers:
- **InMemoryLogReader** - Current request logs
- **FileLogReader** - Text file logs with large file handling
- **DbLogReader** - Database logs with SQL queries

All implement `LogReaderInterface`.

### Resources (Static Content)

**Directory**: `src/Mcp/Resources/`

Serve static content:
- **GuidelinesResource** - Framework guidelines
- **BoostConfigResource** - Package configuration

Each resource provides:
- URI-based addressing
- Content reading
- Serialization

### Transport (I/O Handler)

**File**: `src/Mcp/Transports/StdioTransport.php`

Handles communication:
- Reads JSON-RPC requests from STDIN
- Writes responses to STDOUT
- Callback-based handler pattern
- Clean separation from business logic

Designed for extensibility - can add HTTP, WebSocket, etc.

## Request Flow (Detailed)

```
1. IDE sends JSON-RPC request to STDIN
   {"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"application_info","arguments":{}}}

2. StdioTransport::listen() reads line from STDIN

3. Server::handleRequest() receives raw JSON string
   ├─ json_decode() parses JSON
   ├─ Validate required fields (method, id)
   └─ Log request to mcp-requests.log

4. Server::dispatch() routes method to handler
   ├─ tools/call → callTool()
   ├─ tools/list → listTools()
   ├─ resources/read → readResource()
   └─ other → throw exception

5. Handler executes business logic
   ├─ Get tool instance
   ├─ Validate arguments against schema
   ├─ Call tool->execute(arguments)
   └─ Handle exceptions

6. BaseTool::sanitize() recursively redacts sensitive data
   ├─ Find keys matching sensitive patterns
   ├─ Replace values with ***REDACTED***
   └─ Traverse arrays and objects

7. Format response as JSON-RPC 2.0
   {
     "jsonrpc": "2.0",
     "id": 1,
     "result": {
       "content": [{
         "type": "text",
         "text": "Tool output as JSON"
       }]
     }
   }

8. Log response to mcp-requests.log

9. StdioTransport writes response to STDOUT

10. IDE receives response and processes it
```

## Data Flow

### Tool Execution

```
User Input
    ↓
Parameter Validation (JSON Schema)
    ↓
Tool Logic Execution
    ├─ Yii2 Integration (access components, DB, config)
    ├─ Data Collection
    └─ Result Formatting
    ↓
Sanitization (remove sensitive keys)
    ↓
JSON Serialization
    ↓
MCP Protocol Wrapping
    ↓
STDOUT Output
```

### Log Inspector (Multi-Reader)

```
User Request
    ↓
LogInspectorTool::execute()
    ├─ Parse parameters
    ├─ Discover available readers
    ├─ Query each reader
    │  ├─ InMemoryLogReader::read()
    │  ├─ FileLogReader::read()
    │  └─ DbLogReader::read()
    ├─ Normalize formats
    ├─ Merge results
    ├─ Sort by timestamp
    ├─ Apply pagination
    └─ Build summary
    ↓
Sanitization
    ↓
JSON Output
```

## Security Design

### Automatic Sanitization

Sensitive keys are recursively redacted:

```php
$sensitiveKeys = [
    'password', 'secret', 'key', 'token',
    'api_key', 'private_key', 'auth_key',
    'access_token', 'refresh_token', 'client_secret'
];
```

Happens on:
- All tool outputs
- Configuration data
- Component settings
- Database credentials

### No Shell Execution

- Never use `shell_exec()` for untrusted input
- File operations use PHP functions
- Log tailing uses PHP fopen/fread
- No SQL injection risk (parameterized queries)

### Input Validation

All parameters validated against JSON schemas:
- Type checking
- Constraint validation
- Array size limits
- Numeric ranges

### Error Handling

Errors logged server-side:
- Full details to error logs
- Safe messages to client
- No stack traces exposed
- Connection stays open

## Logging

Four log files for debugging:

| File | Purpose | Content |
|------|---------|---------|
| mcp-startup.log | Initialization | Server start, tool registration |
| mcp-errors.log | PHP errors | Exceptions, warnings, fatals |
| mcp-requests.log | Request/response | JSON-RPC traffic tracing |
| mcp-transport.log | Low-level I/O | Stream debugging |

All logs go to STDERR immediately and files asynchronously.

## Extensibility Points

### Adding a Tool

1. Create class extending `BaseTool`
2. Implement abstract methods
3. Register in `Server::registerTools()`
4. Auto-available via MCP protocol

### Adding a Reader

1. Implement `LogReaderInterface`
2. Instantiate in `LogInspectorTool::getReaders()`
3. Auto-queried when inspecting logs

### Adding a Resource

1. Create class extending `BaseResource`
2. Implement `read()` method
3. Register in `Server::registerResources()`
4. Access via URI

### Adding a Transport

1. Implement `TransportInterface`
2. Update `Server::createTransport()`
3. Could be HTTP, WebSocket, etc.

## Performance Characteristics

### In-Memory Operations
- Application info: Instant (no I/O)
- Component inspector: Instant (component introspection)
- Route inspector: Fast (route table traversal)

### Database Operations
- Database schema: Medium (requires table queries)
- Log inspector (DB): Fast with indexes, slow without

### File Operations
- Log inspector (file): Medium for <100MB, tails for larger
- Config access: Fast (file read)

### Bottlenecks

1. **Large log files** - File tailing slower for huge files
2. **Unindexed database queries** - Log inspector (db) needs indexes
3. **Sensitive data redaction** - Recursive traversal can be slow for huge objects
4. **Response serialization** - Very large JSON responses

## Design Decisions

### Why Stateless?

- Simpler code (no state management)
- Thread-safe
- Can run multiple instances
- Easy to test

### Why STDIO-only?

- Simpler for IDE integration
- No network configuration
- Security (localhost-only)
- Future: HTTP transport can be added

### Why Automatic Sanitization?

- Security by default
- Prevents accidental credential leaks
- Consistent across all tools
- Users don't need to think about it

### Why Multi-Reader for Logs?

- Unified interface regardless of log storage
- Handles different configurations
- Extensible for new backends
- Transparent to users

## Future Architecture

Planned improvements:
- HTTP transport for non-IDE clients
- WebSocket for real-time updates
- Log reader for syslog
- Reader for email logs
- Performance caching layer
- Batch request support
