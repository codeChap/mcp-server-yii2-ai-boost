<?php

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
     * @var resource Error stream resource (for logging)
     */
    private $stderr;

    /**
     * Constructor
     *
     * Opens file handles for STDIN, STDOUT, and STDERR
     */
    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'w');
        $this->stderr = fopen('php://stderr', 'w');

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
                break;
            }

            // Skip empty lines
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                // Call the handler with the request
                $response = $handler($line);

                // Write response back to STDOUT
                if (!empty($response)) {
                    fwrite($this->stdout, $response . "\n");
                    fflush($this->stdout);
                }
            } catch (\Exception $e) {
                // Log error to STDERR
                $this->logError($e->getMessage());
            }
        }
    }

    /**
     * Log an error message to STDERR
     *
     * @param string $message Error message
     */
    private function logError($message)
    {
        $timestamp = date('Y-m-d H:i:s');
        fwrite($this->stderr, "[$timestamp] Error: $message\n");
        fflush($this->stderr);
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
