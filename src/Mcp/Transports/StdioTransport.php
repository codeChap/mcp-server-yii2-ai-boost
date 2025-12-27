<?php

namespace codechap\yii2boost\Mcp\Transports;

use yii\base\Component;

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
class StdioTransport extends Component
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
     * @var resource Error stream resource (for logging)
     */
    private $stderr;

    /**
     * @var resource Log stream for request/response logging
     */
    public $logStream;

    /**
     * @var int Request counter for logging
     */
    private $requestCount = 0;

    /**
     * Initialize transport
     *
     * Opens file handles for STDIN, STDOUT, and STDERR
     */
    public function init()
    {
        parent::init();

        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');

        // If no log stream provided, use stderr
        if (!$this->logStream) {
            $this->logStream = $this->stderr;
        }

        // Disable stream buffering
        if (function_exists('stream_set_blocking')) {
            stream_set_blocking($this->stdin, true);
        }
    }

    /**
     * Start listening for JSON-RPC requests
     *
     * Enters an infinite loop reading JSON-RPC requests from STDIN
     * and writing responses to STDOUT.
     *
     * @param callable $handler Callback to handle requests: function($request) -> string
     */
    public function listen(callable $handler)
    {
        while (true) {
            // Read a line from STDIN
            $line = fgets($this->stdin);

            // Check for EOF or disconnection
            if ($line === false) {
                // Client disconnected, exit gracefully
                $this->logInfo("Client disconnected");
                break;
            }

            // Skip empty lines
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                $this->requestCount++;

                // Log incoming request
                $this->logRequest($line);

                // Call the handler with the request
                $response = $handler($line);

                // Write response back to STDOUT
                if (!empty($response)) {
                    fwrite($this->stdout, $response . "\n");
                    fflush($this->stdout);

                    // Log outgoing response
                    $this->logResponse($response);
                } else {
                    $this->logInfo("No response sent (notification)");
                }
            } catch (\Exception $e) {
                // Log error to log stream
                $this->logError($e->getMessage());
            }
        }
    }

    /**
     * Log incoming JSON-RPC request
     *
     * @param string $request JSON-RPC request string
     */
    private function logRequest($request)
    {
        $timestamp = date('H:i:s');
        $decoded = json_decode($request, true);

        if ($decoded) {
            $method = $decoded['method'] ?? 'unknown';
            $id = $decoded['id'] ?? '(notification)';
            $this->logLine("→ Request #$this->requestCount [$timestamp] Method: $method (ID: $id)");
        } else {
            $this->logLine("→ Request #$this->requestCount [$timestamp] Invalid JSON");
        }
    }

    /**
     * Log outgoing JSON-RPC response
     *
     * @param string $response JSON-RPC response string
     */
    private function logResponse($response)
    {
        $timestamp = date('H:i:s');
        $decoded = json_decode($response, true);

        if ($decoded) {
            $id = $decoded['id'] ?? '(no id)';
            $hasError = isset($decoded['error']);
            $status = $hasError ? '✗ Error' : '✓ Success';
            $this->logLine("← Response [$timestamp] $status (ID: $id)");
        } else {
            $this->logLine("← Response [$timestamp] Invalid JSON");
        }
    }

    /**
     * Log informational message
     *
     * @param string $message Message to log
     */
    private function logInfo($message)
    {
        $timestamp = date('H:i:s');
        $this->logLine("ℹ️  [$timestamp] $message");
    }

    /**
     * Log an error message
     *
     * @param string $message Error message
     */
    private function logError($message)
    {
        $timestamp = date('H:i:s');
        $this->logLine("❌ Error [$timestamp] $message");
    }

    /**
     * Write a line to the log stream
     *
     * @param string $message Message to write
     */
    private function logLine($message)
    {
        fwrite($this->logStream, "  $message\n");
        fflush($this->logStream);
    }

    /**
     * Destructor - close file handles
     */
    public function __destruct()
    {
        if (is_resource($this->stdin)) {
            fclose($this->stdin);
        }
        if (is_resource($this->stdout)) {
            fclose($this->stdout);
        }
        if (is_resource($this->stderr)) {
            fclose($this->stderr);
        }
    }
}
