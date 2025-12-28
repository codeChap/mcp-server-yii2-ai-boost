# Tools Architecture

Understanding how Yii2 AI Boost tools work internally.

## BaseTool Foundation

All 6 core tools extend the `BaseTool` abstract class, which provides:

### Automatic Sanitization
Sensitive data (passwords, tokens, keys) is automatically redacted from all tool outputs. This happens recursively throughout the response data structure.

Redacted keys include:
- `password`, `secret`, `key`
- `token`, `api_key`, `private_key`
- `auth_key`, `access_token`, `refresh_token`
- `client_secret`

### Database Discovery
Tools automatically discover and access all configured database connections without requiring explicit configuration.

### JSON Schema Validation
Input parameters are validated against defined JSON schemas before execution.

### Error Handling
Tools provide graceful error responses that log details server-side without exposing sensitive information to clients.

## Tool Execution Flow

```
Input Request
    ↓
Schema Validation
    ↓
Business Logic Execution
    ↓
Data Collection
    ↓
Sanitization
    ↓
JSON Formatting
    ↓
Response Output
```

## Tool Interface

Each tool implements:

```php
interface Tool {
    public function getName(): string;
    public function getDescription(): string;
    public function getInputSchema(): array;
    public function execute(array $arguments): mixed;
}
```

### getName()
Returns the tool identifier used in `tools/call` requests.

### getDescription()
Returns human-readable description shown in `tools/list`.

### getInputSchema()
Returns JSON Schema for input validation.

### execute()
Performs the actual tool operation with validated arguments.

## Stateless Design

All tools are stateless:
- No internal state between calls
- Results are deterministic based on input parameters
- Thread-safe and concurrent-call safe
- Can be called multiple times with same parameters

## Multi-Reader Architecture (Log Inspector)

The Log Inspector uses a **reader plugin pattern**:

```
LogInspectorTool
    ├─ InMemoryLogReader
    ├─ FileLogReader
    └─ DbLogReader
```

Each reader:
1. Implements `LogReaderInterface`
2. Checks availability (`isAvailable()`)
3. Reads logs independently (`read()`)
4. Returns normalized format

The tool orchestrates readers:
1. Discovers available readers
2. Queries each reader
3. Merges and sorts results
4. Returns aggregated summary

### Reader Interface

```php
interface LogReaderInterface {
    public function read(array $params): array;
    public function isAvailable(): bool;
    public function getSource(): string;
}
```

## Response Format

All tools return content in this MCP protocol format:

```json
{
  "content": [
    {
      "type": "text",
      "text": "Tool output as JSON or formatted text"
    }
  ]
}
```

The `text` field contains:
- JSON for structured data (most tools)
- Formatted text for human-readable output
- Error messages with context

## Error Handling

Exceptions are caught and converted to JSON-RPC error responses:

```json
{
  "error": {
    "code": -32603,
    "message": "Internal error",
    "data": {
      "message": "Tool-specific error details"
    }
  }
}
```

Errors are:
- Logged server-side with full context
- Returned safely without sensitive details
- Descriptive for debugging
- Actionable when possible

## Performance Considerations

### In-Memory Tools
- `application_info` - Instant, no I/O
- `component_inspector` - Instant, component introspection
- `config_access` - Very fast, configuration reading
- `route_inspector` - Fast, route table traversal

### Database Tools
- `database_schema` - Requires DB queries for row counts
- `log_inspector` (db) - Fast with proper indexing

### File I/O Tools
- `log_inspector` (file) - Efficient tail reading for large files

## Extensibility

The architecture supports adding new tools without modifying core logic:

1. Create new class extending `BaseTool`
2. Implement required methods
3. Register in `Server::registerTools()`
4. Tool automatically available via MCP protocol

Similarly, new readers can be added to Log Inspector by:
1. Implementing `LogReaderInterface`
2. Instantiating in `LogInspectorTool::getReaders()`
