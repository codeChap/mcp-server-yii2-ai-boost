<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Transports;

/**
 * STDIO Transport for MCP Protocol
 *
 * Implements the Model Context Protocol using standard input/output.
 * This is the primary transport method for local MCP server integration with IDEs.
 *
 * Communication format:
 * - Each message is a complete JSON string followed by newline
 * - No special framing or length prefixes
 * - Both input and output use this format
 */
class StdioTransport
{
    /**
     * @var resource Input stream resource
     */
    private $stdin;

    /**
     * @var resource Output stream resource
     */
    private $stdout;

    /**
     * @var string Log file path
     */
    private $logFile;

    /**
     * Constructor - initialize streams
     */
    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'w');

        // Initialize log file
        $this->logFile = $this->getLogFile();
        $this->log("StdioTransport initialized");
    }

    /**
     * Get or create log file path
     *
     * @return string
     */
    private function getLogFile(): string
    {
        $runtimeDir = sys_get_temp_dir() . '/mcp-server';
        if (!is_dir($runtimeDir)) {
            @mkdir($runtimeDir, 0755, true);
        }
        return $runtimeDir . '/mcp-transport.log';
    }

    /**
     * Write log message
     *
     * @param string $message Message to log
     * @param string $level Log level (INFO, ERROR, DEBUG)
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    /**
     * Start listening for JSON-RPC requests
     *
     * Enters an infinite loop reading JSON-RPC requests from STDIN
     * and writing responses to STDOUT.
     *
     * @param callable $handler Callback to handle requests: function($request) -> string
     */
    public function listen(callable $handler): void
    {
        $this->log("Starting MCP server listener");

        while (true) {
            // Read a line from STDIN
            $line = fgets($this->stdin);

            // Check for EOF or stream error
            if ($line === false) {
                if (feof($this->stdin)) {
                    // Normal EOF - client disconnected
                    $this->log("Client disconnected (EOF received)", "INFO");
                    break;
                } else {
                    // Stream error
                    $this->log("Failed to read from stdin", "ERROR");
                    fwrite(STDERR, "[MCP ERROR] Failed to read from stdin\n");
                    break;
                }
            }

            // Skip empty lines
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Log incoming request
            $requestPreview = substr($line, 0, 200) . (strlen($line) > 200 ? '...' : '');
            $this->log("Received request: $requestPreview", "DEBUG");

            try {
                // Call the handler with the request
                $response = $handler($line);

                // Write response back to STDOUT
                if (!empty($response)) {
                    $responsePreview = substr($response, 0, 200) . (strlen($response) > 200 ? '...' : '');
                    $this->log("Sending response: $responsePreview", "DEBUG");
                    fwrite($this->stdout, $response . "\n");
                    fflush($this->stdout);
                } else {
                    $this->log("Handler returned empty response (notification)", "DEBUG");
                }
            } catch (\Throwable $e) {
                // Log exception
                $this->log("Handler exception: " . $e->getMessage(), "ERROR");
                $this->log($e->getTraceAsString(), "ERROR");

                // Write error to stderr for debugging
                fwrite(STDERR, "[MCP ERROR] Handler exception: " . $e->getMessage() . "\n");
                fwrite(STDERR, $e->getTraceAsString() . "\n");
                fflush(STDERR);
            }
        }

        $this->log("MCP server listener stopped", "INFO");
    }

    /**
     * Destructor - close file handles
     */
    public function __destruct()
    {
        $this->log("Closing file handles", "INFO");

        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }
        if (is_resource($this->stdout)) {
            fclose($this->stdout);
        }
    }
}
