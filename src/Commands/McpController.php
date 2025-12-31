<?php

declare(strict_types=1);

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
    public function actionIndex(): int
    {
        // Configure logging FIRST to prevent any output buffering issues
        // This must be done before any file operations
        $this->configureLogging();

        try {
            // Log startup event to file for debugging
            $logFile = Yii::getAlias('@runtime/logs/mcp-startup.log');
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            file_put_contents($logFile, date('Y-m-d H:i:s') . " - MCP Server Starting\n", FILE_APPEND);
            file_put_contents($logFile, "  Working Directory: " . getcwd() . "\n", FILE_APPEND);
            file_put_contents($logFile, "  PHP SAPI: " . php_sapi_name() . "\n", FILE_APPEND);
            file_put_contents($logFile, "  Environment: YII_ENV=" . YII_ENV . ", YII_DEBUG=" . (YII_DEBUG ? 'true' : 'false') . "\n", FILE_APPEND);

            // Create and start the MCP server
            $server = new Server([
                'basePath' => Yii::getAlias('@app'),
                'transport' => 'stdio',
            ]);

            file_put_contents($logFile, "  Server initialized, starting listen loop\n", FILE_APPEND);

            // Start the server (infinite loop until client disconnects)
            $server->start();

            file_put_contents($logFile, date('Y-m-d H:i:s') . " - MCP Server Stopped\n", FILE_APPEND);
            return ExitCode::OK;
        } catch (\Throwable $e) {
            // Log error to stderr for debugging
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
    private function configureLogging(): void
    {
        // Suppress all logging during MCP server operation
        // Logging would interfere with JSON-RPC protocol on STDOUT
        error_reporting(E_ALL);
        ini_set('display_errors', '0');
        ini_set('log_errors', '1');

        // Clear any output buffers that may have been started before this script
        // This prevents buffered content from interfering with JSON-RPC responses
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Log errors both to file and stderr for better debugging
        $errorLogFile = Yii::getAlias('@runtime/logs/mcp-errors.log');
        ini_set('error_log', $errorLogFile);

        // Set custom error handler to also log to stderr
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            fwrite(STDERR, "[PHP Error] $errstr in $errfile:$errline\n");
            error_log("$errstr in $errfile:$errline");
            return true;
        });
    }
}
