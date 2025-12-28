# Installation

Get Yii2 AI Boost up and running in your Yii2 application.

## Requirements

- **PHP** 7.4 or higher
- **Yii2** 2.0.45 or higher
- **Composer** for dependency management

## Step 1: Install via Composer

```bash
cd /path/to/yii2/application
composer require codechap/yii2-ai-boost --dev
```

This installs the package as a development dependency (won't be shipped to production).

## Step 2: Run Installation Wizard

```bash
php yii boost/install
```

The wizard will:
- ✓ Detect your Yii2 environment (web or console)
- ✓ Generate configuration files
- ✓ Create guidelines directory
- ✓ Auto-configure IDE integration (if available)

## Generated Files

After installation, you'll have:

### `.mcp.json`
MCP server configuration for IDEs (Claude Code, VS Code, PhpStorm, Cursor). This file is auto-generated and updated by the installer.

### `boost.json`
Package configuration and tool metadata. Includes version info and available tools.

### `CLAUDE.md`
Application guidelines wrapper that includes framework instructions and best practices.

### `.ai/guidelines/`
Directory containing downloaded guideline files for Yii2 framework and ecosystem packages.

## Step 3: Verify Installation

```bash
php yii boost/info
```

This displays:
- Package version and configuration
- List of available MCP tools (should show 6)
- Status of guidelines and configuration files

You should see output like:

```
Yii2 AI Boost - Installation Information

Package Version: 1.0.0
Yii2 Version: 2.0.45
Environment: development

Available Tools (6):
  ✓ application_info
  ✓ database_schema
  ✓ config_access
  ✓ route_inspector
  ✓ component_inspector
  ✓ log_inspector

Configuration Files:
  ✓ .mcp.json
  ✓ boost.json
  ✓ CLAUDE.md
```

## Step 4: IDE Configuration

### Claude Code
The installer auto-configures Claude Code if available. No additional setup needed.

### VS Code
Add this to your VS Code `claude.json` or `.vscode/claude.json`:

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

### Zed
Edit `.zed/settings.json`:

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

### PhpStorm / JetBrains IDEs
Configure in Settings → Tools → MCP Servers:
- Command: `php`
- Arguments: `yii boost/mcp`
- Working Directory: `/path/to/your/app`

## Troubleshooting

### "Command not found" error
Ensure the `yii` command is executable. If using a custom console entry point, use its path instead:

```bash
php console.php boost/install
```

### Missing `.mcp.json` file
Re-run the installer:

```bash
php yii boost/install
```

### Permission issues
Ensure the application has write permissions to:
- Root directory (for `.mcp.json`)
- `@runtime/` directory (for logs)

## Uninstalling

To remove Yii2 AI Boost:

```bash
composer remove codechap/yii2-ai-boost --dev
rm .mcp.json boost.json CLAUDE.md
rm -rf .ai/
```

## Next Steps

- Read the [Getting Started](getting-started.md) guide
- Explore individual [tools documentation](tools/index.md)
- Check out [cookbook recipes](../cookbook/index.md) for common tasks
