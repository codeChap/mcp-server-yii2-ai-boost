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

            // Create and start the MCP server
            $server = new Server([
                'basePath' => Yii::getAlias('@app'),
                'transport' => 'stdio',
            ]);

            // Start the server (infinite loop until client disconnects)
            $server->start();

            return ExitCode::OK;
        } catch (\Exception $e) {
            // Log error to stderr
            fwrite(STDERR, "MCP Server Error: " . $e->getMessage() . "\n");
            fwrite(STDERR, $e->getTraceAsString() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
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
