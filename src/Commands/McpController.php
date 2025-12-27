<?php

namespace codechap\yii2boost\Commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use codechap\yii2boost\Mcp\Server;

/**
 * MCP Server Command
 *
 * Starts the Model Context Protocol (MCP) server via STDIO transport.
 * This command is typically invoked automatically by MCP clients (IDEs).
 *
 * Usage:
 *   php yii boost/mcp
 *
 * Note: This command should NOT be run manually in interactive terminals.
 */
class McpController extends Controller
{
    /**
     * Start the MCP server
     *
     * The server runs in STDIO mode, reading JSON-RPC requests from STDIN
     * and writing responses to STDOUT. All logging goes to STDERR.
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        try {
            // Configure logging to stderr only to avoid interfering with STDOUT JSON-RPC
            $this->configureLogging();

            // Print startup information (optional - can be disabled for debugging)
            if (getenv('YII2_BOOST_MCP_DEBUG') !== 'false') {
                $this->printStartupInfo();
            }

            // Create and start the MCP server
            $server = new Server([
                'basePath' => Yii::getAlias('@app'),
                'transport' => 'stdio',
                'logStream' => STDERR,
            ]);

            // Start the server (infinite loop until client disconnects)
            $server->start();

            return ExitCode::OK;
        } catch (\Exception $e) {
            // Log error to stderr
            fwrite(STDERR, "❌ MCP Server Error: " . $e->getMessage() . "\n");
            fwrite(STDERR, $e->getTraceAsString() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Print startup information to STDERR
     */
    private function printStartupInfo()
    {
        $basePath = Yii::getAlias('@app');
        $timestamp = date('Y-m-d H:i:s');

        fwrite(STDERR, "\n");
        fwrite(STDERR, "╔════════════════════════════════════════════════════════════╗\n");
        fwrite(STDERR, "║           Yii2 AI Boost - MCP Server Started               ║\n");
        fwrite(STDERR, "╚════════════════════════════════════════════════════════════╝\n\n");

        fwrite(STDERR, "  📍 Server Information\n");
        fwrite(STDERR, "  ────────────────────────────────────────────────────────────\n");
        fwrite(STDERR, "  Status:     ✓ Started at $timestamp\n");
        fwrite(STDERR, "  Transport:  STDIO (reads from stdin, writes to stdout)\n");
        fwrite(STDERR, "  Protocol:   JSON-RPC 2.0\n");
        fwrite(STDERR, "  App Path:   $basePath\n");
        fwrite(STDERR, "  Tools:      5 available\n\n");

        fwrite(STDERR, "  🔌 MCP Client Instructions\n");
        fwrite(STDERR, "  ────────────────────────────────────────────────────────────\n");
        fwrite(STDERR, "  Send JSON-RPC 2.0 requests on stdin.\n");
        fwrite(STDERR, "  Each request must be a complete JSON object.\n");
        fwrite(STDERR, "  Example:\n");
        fwrite(STDERR, "    {\"jsonrpc\":\"2.0\",\"id\":1,\"method\":\"tools/list\"}\n\n");

        fwrite(STDERR, "  📋 Request Log (all times in local timezone)\n");
        fwrite(STDERR, "  ────────────────────────────────────────────────────────────\n");
    }

    /**
     * Configure logging to prevent interference with STDOUT
     *
     * All application logs go to STDERR or a file, never to STDOUT.
     * This ensures STDOUT remains clean for JSON-RPC messages.
     */
    private function configureLogging()
    {
        // Suppress all logging during MCP server operation
        // Logging would interfere with JSON-RPC protocol on STDOUT
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');
        ini_set('error_log', Yii::getAlias('@runtime/logs/mcp-errors.log'));
    }
}
