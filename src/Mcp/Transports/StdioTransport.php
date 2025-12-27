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
     * Constructor - initialize streams
     */
    public function __construct()
    {
        $this->stdin = fopen('php://stdin', 'r');
        $this->stdout = fopen('php://stdout', 'w');
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
                // Silently ignore errors to keep stdout clean
            }
        }
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
    }
}
