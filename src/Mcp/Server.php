<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 * MCP Server for Yii2 Applications
 *
 * Provides AI assistants with tools and resources for Yii2 development.
 * Implements the Model Context Protocol (MCP) for communication via JSON-RPC.
 */
class Server extends Component
{
    /**
     * @var string Base path to the Yii2 application
     */
    public $basePath;

    /**
     * @var string Transport type ('stdio' or 'http')
     */
    public $transport = 'stdio';


    /**
     * @var array Collection of registered tools
     */
    private $tools = [];

    /**
     * @var array Collection of registered resources
     */
    private $resources = [];

    /**
     * @var Transports\TransportInterface Transport instance
     */
    private $transportInstance;

    /**
     * @var string Log file path
     */
    private $logFile;

    /**
     * Initialize the MCP server
     *
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        // Initialize log file
        $this->logFile = Yii::getAlias('@runtime/logs/mcp-requests.log');
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $this->log("=== MCP Server Initialization Started ===");
        $this->log("Base path: {$this->basePath}");
        $this->log("Transport: {$this->transport}");

        // Initialize all tools
        $this->registerTools();

        // Initialize resources
        $this->registerResources();

        // Create transport instance
        $this->createTransport();

        $this->log("=== MCP Server Initialization Complete ===");
    }

    /**
     * Write log message
     *
     * @param string $message Message to log
     */
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    /**
     * Start the MCP server
     *
     * This method enters an infinite loop listening for JSON-RPC requests
     * over the configured transport (STDIO or HTTP).
     *
     * @throws Exception
     */
    public function start(): void
    {
        if (!$this->transportInstance) {
            throw new Exception('Transport not initialized');
        }

        // Enter listen loop
        $this->transportInstance->listen(function ($request) {
            return $this->handleRequest($request);
        });
    }

    /**
     * Handle incoming JSON-RPC request
     *
     * @param string $request JSON-RPC request string
     * @return string JSON-RPC response string
     */
    public function handleRequest(string $request): string
    {
        // Initialize decoded to avoid undefined variable in catch block
        $decoded = null;

        $this->log("--- Incoming Request ---");
        $this->log("Raw request: " . substr($request, 0, 300) . (strlen($request) > 300 ? '...' : ''));

        try {
            $decoded = json_decode($request, true);

            // Allow requests without jsonrpc field for compatibility with newer MCP versions
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->log("JSON Parse Error: " . json_last_error_msg(), "ERROR");
                return json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32700,
                        'message' => 'Parse error',
                    ],
                ]);
            }

            $this->log("Decoded JSON: " . json_encode($decoded));

            if (!isset($decoded['method'])) {
                $this->log("Invalid Request: Missing method field", "ERROR");
                return json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32600,
                        'message' => 'Invalid Request',
                    ],
                    'id' => $decoded['id'] ?? null,
                ]);
            }

            $method = $decoded['method'];
            $params = $decoded['params'] ?? [];
            $id = $decoded['id'] ?? null;

            $this->log("Method: $method");
            $this->log("Params: " . json_encode($params));
            $this->log("ID: " . ($id ?? 'null'));

            // Check if this is a notification (no id field means it's a notification)
            $isNotification = !isset($decoded['id']);

            // Handle notifications - they don't expect a response
            if ($isNotification) {
                $this->log("Processing as notification");
                $this->handleNotification($method, $params);
                return ''; // Notifications don't return a response
            }

            $this->log("Dispatching method: $method");
            $result = $this->dispatch($method, $params);

            // Always return a response. Claude Code doesn't always send an id field,
            // but still expects responses to all requests.
            $response = json_encode([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ]);

            $this->log("--- Response ---");
            $this->log("Response: " . substr($response, 0, 300) . (strlen($response) > 300 ? '...' : ''));

            return $response;
        } catch (\Throwable $e) {
            $id = isset($decoded['id']) ? $decoded['id'] : null;

            // Log exception
            $this->log("Exception caught: " . $e->getMessage(), "ERROR");
            $this->log($e->getTraceAsString(), "ERROR");

            // Log exception details to stderr for debugging
            fwrite(STDERR, "[MCP Exception] " . $e->getMessage() . "\n");
            fwrite(STDERR, $e->getTraceAsString() . "\n");

            $response = json_encode([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => 'Internal error',
                    'data' => [
                        'message' => $e->getMessage(),
                    ],
                ],
            ]);

            $this->log("Error Response: " . substr($response, 0, 300) . (strlen($response) > 300 ? '...' : ''), "ERROR");

            return $response;
        }
    }

    /**
     * Handle MCP notifications (requests without an id field)
     *
     * Notifications are one-way messages that don't expect a response.
     * Common notifications:
     * - notifications/initialized: Client signals it received initialize response
     * - notifications/progress: Client reports progress on operations
     *
     * @param string $method Notification method name
     * @param array $params Notification parameters
     */
    private function handleNotification(string $method, array $params): void
    {
        // Log notification
        $this->log("Handling notification: $method");
        $this->log("Notification params: " . json_encode($params));

        // Handle specific notifications
        switch ($method) {
            case 'notifications/initialized':
                // Client has received our initialize response and is ready
                $this->log("Client initialized and ready");
                break;

            case 'notifications/progress':
                // Client reporting progress - we can ignore for server-side tools
                $this->log("Client progress notification");
                break;

            default:
                // Unknown notification - log but don't error
                $this->log("Unknown notification: $method");
                break;
        }
    }

    /**
     * Dispatch JSON-RPC method call
     *
     * @param string $method Method name
     * @param array $params Method parameters
     * @return mixed Result
     * @throws Exception
     */
    private function dispatch(string $method, array $params): mixed
    {
        $this->log("Dispatch: $method");

        switch ($method) {
            case 'initialize':
                $this->log("Calling initialize");
                return $this->initialize($params);

            case 'tools/list':
                $this->log("Calling listTools");
                return $this->listTools();

            case 'tools/call':
                $name = $params['name'] ?? null;
                $arguments = $params['arguments'] ?? [];
                $this->log("Calling tool: $name");
                $this->log("Tool arguments: " . json_encode($arguments));
                return $this->callTool($name, $arguments);

            case 'resources/list':
                $this->log("Calling listResources");
                return $this->listResources();

            case 'resources/read':
                $uri = $params['uri'] ?? null;
                $this->log("Reading resource: $uri");
                return $this->readResource($uri);

            default:
                throw new Exception("Unknown method: $method");
        }
    }

    /**
     * Initialize the MCP server connection
     *
     * Called by the client at the start of the connection.
     *
     * @param array $params Client initialization parameters
     * @return array Server capabilities
     */
    private function initialize(array $params): array
    {
        // Use the client's protocol version if provided, otherwise use our version
        $clientProtocolVersion = $params['protocolVersion'] ?? null;
        $protocolVersion = $clientProtocolVersion ?: '2025-11-25';

        return [
            'protocolVersion' => $protocolVersion,
            'capabilities' => [
                'tools' => new \stdClass(),      // Empty object, not array
                'resources' => new \stdClass(),  // Empty object, not array
            ],
            'serverInfo' => [
                'name' => 'Yii2 AI Boost',
                'version' => '1.0.0',
            ],
        ];
    }

    /**
     * List all available tools
     *
     * @return array
     */
    private function listTools(): array
    {
        $this->log("Listing " . count($this->tools) . " available tools");
        $tools = [];
        foreach ($this->tools as $name => $tool) {
            $this->log("  - Tool: $name");
            $tools[] = [
                'name' => $name,
                'description' => $tool->getDescription(),
                'inputSchema' => $tool->getInputSchema(),
            ];
        }
        return ['tools' => $tools];
    }

    /**
     * Call a specific tool
     *
     * @param string $name Tool name
     * @param array $arguments Tool arguments
     * @return mixed Tool result
     * @throws Exception
     */
    private function callTool(string $name, array $arguments): mixed
    {
        if (!isset($this->tools[$name])) {
            $this->log("Tool not found: $name", "ERROR");
            throw new Exception("Unknown tool: $name");
        }

        $this->log("Executing tool: $name");
        $tool = $this->tools[$name];
        $result = $tool->execute($arguments);
        $this->log("Tool execution complete: $name");

        // Format result for MCP protocol
        $text = is_string($result) ? $result : json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return [
            'content' => [
                [
                    'type' => 'text',
                    'text' => $text,
                ]
            ]
        ];
    }

    /**
     * List all available resources
     *
     * @return array
     */
    private function listResources(): array
    {
        $this->log("Listing " . count($this->resources) . " available resources");
        $resources = [];
        foreach ($this->resources as $uri => $resource) {
            $this->log("  - Resource: $uri");
            $resources[] = [
                'uri' => $uri,
                'name' => $resource->getName(),
                'description' => $resource->getDescription(),
            ];
        }
        return ['resources' => $resources];
    }

    /**
     * Read a specific resource
     *
     * @param string $uri Resource URI
     * @return mixed Resource content
     * @throws Exception
     */
    private function readResource(string $uri): mixed
    {
        if (!$uri) {
            $this->log("Resource URI is required", "ERROR");
            throw new Exception("Resource URI is required");
        }

        if (!isset($this->resources[$uri])) {
            $this->log("Resource not found: $uri", "ERROR");
            throw new Exception("Unknown resource: $uri");
        }

        $this->log("Reading resource: $uri");
        $resource = $this->resources[$uri];
        $data = $resource->read();
        $this->log("Resource read complete: $uri");

        // Map internal type to MIME type
        $mimeType = 'text/plain';
        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'json':
                    $mimeType = 'application/json';
                    break;
                case 'markdown':
                    $mimeType = 'text/markdown';
                    break;
                case 'text':
                default:
                    $mimeType = 'text/plain';
                    break;
            }
        }

        return [
            'contents' => [
                [
                    'uri' => $uri,
                    'mimeType' => $mimeType,
                    'text' => $data['content'] ?? '',
                ]
            ]
        ];
    }

    /**
     * Register all MCP tools
     */
    private function registerTools(): void
    {
        $this->log("Registering MCP tools");
        $toolClasses = [
            Tools\ApplicationInfoTool::class,
            Tools\DatabaseSchemaTool::class,
            Tools\ConfigAccessTool::class,
            Tools\RouteInspectorTool::class,
            Tools\ComponentInspectorTool::class,
        ];

        foreach ($toolClasses as $class) {
            try {
                $tool = new $class(['basePath' => $this->basePath]);
                $this->tools[$tool->getName()] = $tool;
                $this->log("  ✓ Registered tool: " . $tool->getName());
            } catch (\Exception $e) {
                $this->log("  ✗ Failed to register tool $class: " . $e->getMessage(), "ERROR");
            }
        }
        $this->log("Tool registration complete. Total: " . count($this->tools) . " tools");
    }

    /**
     * Register MCP resources
     */
    private function registerResources(): void
    {
        $this->log("Registering MCP resources");

        try {
            $this->resources['guidelines://core'] = new Resources\GuidelinesResource([
                'basePath' => $this->basePath,
            ]);
            $this->log("  ✓ Registered resource: guidelines://core");
        } catch (\Exception $e) {
            $this->log("  ✗ Failed to register guidelines resource: " . $e->getMessage(), "ERROR");
        }

        try {
            $this->resources['config://boost'] = new Resources\BoostConfigResource([
                'basePath' => $this->basePath,
            ]);
            $this->log("  ✓ Registered resource: config://boost");
        } catch (\Exception $e) {
            $this->log("  ✗ Failed to register config resource: " . $e->getMessage(), "ERROR");
        }

        $this->log("Resource registration complete. Total: " . count($this->resources) . " resources");
    }

    /**
     * Create transport instance based on configuration
     *
     * @throws Exception
     */
    private function createTransport(): void
    {
        switch ($this->transport) {
            case 'stdio':
                $this->transportInstance = new Transports\StdioTransport($this->basePath);
                break;

            default:
                throw new Exception("Unknown transport: {$this->transport}");
        }
    }

    /**
     * Get registered tools (for debugging/info)
     *
     * @return array
     */
    public function getTools(): array
    {
        return $this->tools;
    }

    /**
     * Get registered resources (for debugging/info)
     *
     * @return array
     */
    public function getResources(): array
    {
        return $this->resources;
    }
}
