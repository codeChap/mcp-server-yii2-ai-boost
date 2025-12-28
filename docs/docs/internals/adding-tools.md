# Adding Tools

Create new tools to extend Yii2 AI Boost.

## Tool Development

### Step 1: Create Tool Class

Create a new file: `src/Mcp/Tools/MyNewTool.php`

```php
<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

class MyNewTool extends BaseTool
{
    public function getName(): string
    {
        return 'my_tool';  // Used in MCP calls
    }

    public function getDescription(): string
    {
        return 'Brief description of what the tool does';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'param1' => [
                    'type' => 'string',
                    'description' => 'First parameter',
                ],
                'param2' => [
                    'type' => 'integer',
                    'description' => 'Second parameter',
                    'minimum' => 1,
                    'maximum' => 100,
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $param1 = $arguments['param1'] ?? null;
        $param2 = $arguments['param2'] ?? 10;

        // Your business logic here
        $result = $this->doSomething($param1, $param2);

        // Sanitize before returning
        return $this->sanitize($result);
    }

    private function doSomething(string $param1, int $param2): array
    {
        // Implementation
        return [
            'param1' => $param1,
            'param2' => $param2,
            'result' => 'some data',
        ];
    }
}
```

### Step 2: Register Tool

Edit `src/Mcp/Server.php`, find `registerTools()` method:

```php
private function registerTools(): void
{
    $this->log("Registering MCP tools");
    $toolClasses = [
        Tools\ApplicationInfoTool::class,
        // ... other tools ...
        Tools\MyNewTool::class,  // Add here
    ];
    // ... rest of method ...
}
```

### Step 3: Test Tool

Call your tool:

```bash
php yii boost/mcp <<'EOF'
{"jsonrpc":"2.0","id":1,"method":"tools/call","params":{"name":"my_tool","arguments":{"param1":"test","param2":50}}}
EOF
```

## Input Schema

Define what parameters your tool accepts using JSON Schema.

### Common Types

```php
'type' => 'string',              // Text
'type' => 'integer',             // Whole number
'type' => 'number',              // Decimal number
'type' => 'boolean',             // true/false
'type' => 'array',               // List
'type' => 'object',              // Key-value pairs
```

### Constraints

```php
// String length
'minLength' => 1,
'maxLength' => 255,

// Number range
'minimum' => 0,
'maximum' => 100,

// Array items
'items' => ['type' => 'string'],

// Enum (specific values)
'enum' => ['red', 'green', 'blue'],

// Default value
'default' => 'some_value',

// Required
// (specify in top-level 'required' array)
```

### Example: Complex Schema

```php
public function getInputSchema(): array
{
    return [
        'type' => 'object',
        'required' => ['database', 'table'],  // Required params
        'properties' => [
            'database' => [
                'type' => 'string',
                'description' => 'Database connection name',
            ],
            'table' => [
                'type' => 'string',
                'description' => 'Table name to inspect',
            ],
            'limit' => [
                'type' => 'integer',
                'description' => 'Max rows to return',
                'minimum' => 1,
                'maximum' => 1000,
                'default' => 100,
            ],
            'columns' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
                'description' => 'Specific columns to include',
            ],
        ],
    ];
}
```

## Using BaseTool Utilities

### Database Connections

Get all configured database connections:

```php
$connections = $this->getDbConnections();
// Returns:
// [
//     'main' => [
//         'dsn' => 'mysql:host=localhost;dbname=myapp',
//         'driver' => 'mysql',
//         'username' => 'root'
//     ]
// ]
```

### Data Sanitization

Automatically redact sensitive data:

```php
$data = [
    'username' => 'john',
    'password' => 'secret123',
    'api_key' => 'abc123def456',
];

$safe = $this->sanitize($data);
// Returns:
// [
//     'username' => 'john',
//     'password' => '***REDACTED***',
//     'api_key' => '***REDACTED***'
// ]
```

Redacted keys include: password, secret, key, token, api_key, private_key, auth_key, access_token, refresh_token, client_secret.

## Error Handling

Throw exceptions - they're caught and converted to JSON-RPC errors:

```php
public function execute(array $arguments): mixed
{
    $database = $arguments['database'] ?? null;
    
    if (!$database) {
        throw new \Exception('Database parameter is required');
    }
    
    if (!Yii::$app->has($database)) {
        throw new \Exception("Database connection '$database' not found");
    }
    
    // Continue with logic...
}
```

## Best Practices

### 1. Validate Parameters

Check for required parameters and valid values:

```php
$limit = (int) ($arguments['limit'] ?? 100);

if ($limit < 1 || $limit > 1000) {
    throw new \Exception('Limit must be between 1 and 1000');
}
```

### 2. Sanitize Output

Always sanitize before returning:

```php
return $this->sanitize([
    'data' => $result,
    'summary' => $summary,
]);
```

### 3. Handle Missing Components

Don't assume components exist:

```php
if (!Yii::$app->has('db')) {
    return [
        'error' => 'Database component not configured',
        'data' => [],
    ];
}
```

### 4. Use Meaningful Descriptions

Help users understand parameters:

```php
'categories' => [
    'type' => 'array',
    'items' => ['type' => 'string'],
    'description' => 'Category patterns (supports wildcards: app\\*, yii\\db\\*)',
],
```

### 5. Provide Reasonable Defaults

```php
'limit' => [
    'type' => 'integer',
    'default' => 100,
    'maximum' => 1000,
],
```

### 6. Return Structured Data

Always return consistent JSON structure:

```php
return [
    'data' => $results,
    'summary' => [
        'total' => count($results),
        'time_taken' => 0.234,
    ],
    'errors' => [],
];
```

## Testing Your Tool

### Manual Testing

```bash
# Test basic call
php yii boost/mcp <<'EOF'
{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}
EOF

# Your tool should appear in the list with name, description, schema
```

### Unit Tests

Create `tests/Unit/Tools/MyNewToolTest.php`:

```php
<?php

namespace codechap\yii2boost\Tests\Unit\Tools;

use PHPUnit\Framework\TestCase;
use codechap\yii2boost\Mcp\Tools\MyNewTool;

class MyNewToolTest extends TestCase
{
    private MyNewTool $tool;

    protected function setUp(): void
    {
        $this->tool = new MyNewTool();
    }

    public function testGetName(): void
    {
        $this->assertEquals('my_tool', $this->tool->getName());
    }

    public function testExecuteWithValidArguments(): void
    {
        $result = $this->tool->execute([
            'param1' => 'test',
            'param2' => 50,
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
    }

    public function testExecuteWithoutRequiredParameter(): void
    {
        $this->expectException(\Exception::class);
        
        $this->tool->execute([
            'param2' => 50,
        ]);
    }
}
```

Run tests:

```bash
composer test
```

## Advanced: Adding a New Log Reader

If you want to add a new log reader (e.g., for Sentry, CloudWatch):

1. Create `src/Mcp/Tools/Readers/MyLogReader.php`
2. Implement `LogReaderInterface`
3. Add to `LogInspectorTool::getReaders()`

See [Adding Readers](adding-readers.md) for details.

## Examples

See existing tools in `src/Mcp/Tools/`:
- `ApplicationInfoTool.php` - Simple tool
- `DatabaseSchemaTool.php` - Complex tool with multiple methods
- `LogInspectorTool.php` - Multi-reader orchestrator

## Common Patterns

### Listing Things

```php
public function execute(array $arguments): mixed
{
    $items = [];
    
    // Collect items
    foreach ($this->getAllItems() as $item) {
        $items[$item->id] = [
            'id' => $item->id,
            'name' => $item->name,
            'status' => $item->status,
        ];
    }
    
    return $this->sanitize([
        'items' => $items,
        'total' => count($items),
    ]);
}
```

### Filtering & Pagination

```php
public function execute(array $arguments): mixed
{
    $limit = (int) ($arguments['limit'] ?? 100);
    $offset = (int) ($arguments['offset'] ?? 0);
    $filter = $arguments['filter'] ?? null;
    
    $query = $this->buildQuery($filter);
    $total = $query->count();
    $items = $query->limit($limit)->offset($offset)->all();
    
    return $this->sanitize([
        'items' => $items,
        'total' => $total,
        'limit' => $limit,
        'offset' => $offset,
    ]);
}
```

## Next Steps

- Review [Architecture](architecture.md) for system design
- Check [Adding Readers](adding-readers.md) for log reader extension
- See [Contributing](contributing.md) for pull request guidelines
