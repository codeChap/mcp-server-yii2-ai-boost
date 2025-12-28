# IDE Setup

Configure Yii2 AI Boost with your favorite IDE.

## Automatic Setup

The installation wizard automatically configures your IDE if possible:

```bash
php yii boost/install
```

If auto-configuration succeeds, you're done! Skip to [Testing](#testing).

## Manual IDE Configuration

If auto-configuration didn't work, follow the manual steps for your IDE.

### Claude Code

No additional setup needed. The installer should have configured everything.

To verify, start Claude Code and ask it a question about your app.

### VS Code with Claude Extension

Edit your VS Code settings (`.vscode/claude.json` or workspace settings):

```json
{
  "mcpServers": {
    "yii2-boost": {
      "command": "php",
      "args": ["yii", "boost/mcp"],
      "cwd": "/path/to/your/app"
    }
  }
}
```

**Important**: Replace `/path/to/your/app` with your actual application path.

### Zed Editor

Edit `.zed/settings.json` in your project root:

```json
{
  "context_servers": {
    "yii2-boost": {
      "enabled": true,
      "command": "php",
      "args": ["yii", "boost/mcp"]
    }
  }
}
```

Zed automatically uses your project root as the working directory.

### PhpStorm / JetBrains IDEs

Configure in IDE settings:

1. Open **Settings** (Preferences on macOS)
2. Navigate to **Tools → MCP Servers**
3. Click **Add Configuration**
4. Fill in:
   - **Command**: `php`
   - **Arguments**: `yii boost/mcp`
   - **Working Directory**: `/path/to/your/app`
5. Click **OK**

### Cursor Editor

Edit `.cursor/settings.json`:

```json
{
  "mcpServers": {
    "yii2-boost": {
      "command": "php",
      "args": ["yii", "boost/mcp"],
      "cwd": "/path/to/your/app"
    }
  }
}
```

## Testing Configuration

### Test 1: Simple Query

Ask your AI assistant: "What version of Yii2 am I using?"

If it responds with your Yii2 version, the integration is working!

### Test 2: Database Info

Ask: "Show me my database tables"

It should list your tables with row counts.

### Test 3: Error Message

Ask: "Show me recent errors in my logs"

It should display your application's recent errors.

### Test 4: Route Info

Ask: "What routes do I have?"

It should list your application's URL rules.

## Troubleshooting

### "Server not responding"

1. Verify the `yii` command works:
```bash
php yii boost/info
```

2. Check the working directory is correct
3. Verify PHP is in your PATH
4. Check for syntax errors in your IDE config file

### "Command not found: yii"

The installer may have used a different entry point. Try:

```bash
php console.php boost/mcp
```

And update your IDE config accordingly.

### "Permission denied"

Ensure the `yii` file or `console.php` is executable:

```bash
chmod +x yii
```

### "Socket/Port Error"

If you see socket or port errors, try restarting your IDE.

The MCP server listens on STDIO, not a network port.

### "Still Not Working?"

Check if the server starts manually:

```bash
php yii boost/mcp
```

If it hangs waiting for input, that's correct! Press Ctrl+C to exit.

If you see errors, they're logged to:
```
@runtime/logs/mcp-errors.log
@runtime/logs/mcp-startup.log
```

Check those logs for details.

## Working with Multiple Projects

Each project needs its own configuration.

### Create Project-Specific Config

Instead of global IDE config, use workspace/project settings:

**VS Code** - Create `.vscode/settings.json` in project root:
```json
{
  "claude": {
    "mcpServers": {
      "yii2-boost": {
        "command": "php",
        "args": ["yii", "boost/mcp"],
        "cwd": "${workspaceFolder}"
      }
    }
  }
}
```

**Zed** - Create `.zed/settings.json`:
```json
{
  "context_servers": {
    "yii2-boost": {
      "enabled": true,
      "command": "php",
      "args": ["yii", "boost/mcp"]
    }
  }
}
```

The `${workspaceFolder}` or project root is used automatically.

## Environment Variables

If your application needs environment variables:

**VS Code/Cursor**:
```json
{
  "mcpServers": {
    "yii2-boost": {
      "command": "php",
      "args": ["yii", "boost/mcp"],
      "cwd": "/path/to/app",
      "env": {
        "YII_ENV": "dev",
        "YII_DEBUG": "1"
      }
    }
  }
}
```

**Zed**: Zed inherits environment from the IDE process.

## Performance Tips

1. **Limit log queries** - Use `limit` parameter to prevent huge responses
2. **Filter by time range** - When querying many logs
3. **Use specific targets** - Query `db` instead of `all` if you know the source
4. **Keep IDE responsive** - Large responses may slow down autocomplete

## Common Issues

### Slow Responses

The MCP server makes actual database queries and file I/O.

- Large log files may take time to parse
- Database queries with many results are slow
- File tailing for 5MB+ files takes a moment

This is normal. Use pagination (`limit`, `offset`) to speed up responses.

### IDE Disconnects

If the IDE disconnects:

1. Check application logs
2. Verify PHP is still running
3. Restart the IDE
4. Re-run `php yii boost/install` if needed

### Different Behavior in Different IDEs

Some IDEs have different MCP implementations. If something works in one IDE but not another:

1. Check if the command and arguments are correct
2. Try a simple tool first (like `application_info`)
3. Check IDE-specific MCP documentation

## Next Steps

- Read the [Guide](../guide/index.md) for complete tool documentation
- Check out [Cookbook recipes](index.md) for common tasks
- See [Debugging with Logs](debugging-with-logs.md) for practical examples
