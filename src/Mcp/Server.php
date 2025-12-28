<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp;

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
     * Initialize the MCP server
     *
     * @throws Exception
     */
    public function init(): void
    {
        parent::init();

        // Initialize all tools
        $this->registerTools();

        // Initialize resources
        $this->registerResources();

        // Create transport instance
        $this->createTransport();
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
        // Log request to file for debugging
        $logFile = \Yii::getAlias('@runtime/logs/mcp-requests.log');
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Request: " . substr($request, 0, 100) . (strlen($request) > 100 ? '...' : '') . "\n", FILE_APPEND);

        try {
            $decoded = json_decode($request, true);

            // Allow requests without jsonrpc field for compatibility with newer MCP versions
            if (json_last_error() !== JSON_ERROR_NONE) {
                return json_encode([
                    'jsonrpc' => '2.0',
                    'error' => [
                        'code' => -32700,
                        'message' => 'Parse error',
                    ],
                ]);
            }

            if (!isset($decoded['method'])) {
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

            // Check if this is a notification (no id field means it's a notification)
            $isNotification = !isset($decoded['id']);

            // Handle notifications - they don't expect a response
            if ($isNotification) {
                $this->handleNotification($method, $params);
                return ''; // Notifications don't return a response
            }

            $result = $this->dispatch($method, $params);

            // Always return a response. Claude Code doesn't always send an id field,
            // but still expects responses to all requests.
            $response = json_encode([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ]);

            // Log response
            $logFile = \Yii::getAlias('@runtime/logs/mcp-requests.log');
            file_put_contents($logFile, "  Response: " . substr($response, 0, 100) . (strlen($response) > 100 ? '...' : '') . "\n", FILE_APPEND);

            return $response;
        } catch (\Exception $e) {
            $id = isset($decoded['id']) ? $decoded['id'] : null;

            // Log exception details to stderr for debugging
            fwrite(STDERR, "[MCP Exception] " . $e->getMessage() . "\n");
            fwrite(STDERR, $e->getTraceAsString() . "\n");

            // Log to file as well
            $logFile = \Yii::getAlias('@runtime/logs/mcp-requests.log');
            file_put_contents($logFile, "  ERROR: " . $e->getMessage() . "\n", FILE_APPEND);

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

            file_put_contents($logFile, "  Error Response: " . substr($response, 0, 100) . (strlen($response) > 100 ? '...' : '') . "\n", FILE_APPEND);

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
        $logFile = \Yii::getAlias('@runtime/logs/mcp-requests.log');
        file_put_contents($logFile, "  Notification: $method\n", FILE_APPEND);

        // Handle specific notifications
        switch ($method) {
            case 'notifications/initialized':
                // Client has received our initialize response and is ready
                // No action needed
                break;

            case 'notifications/progress':
                // Client reporting progress - we can ignore for server-side tools
                break;

            default:
                // Unknown notification - log but don't error
                file_put_contents($logFile, "  Unknown notification: $method\n", FILE_APPEND);
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
        switch ($method) {
            case 'initialize':
                return $this->initialize($params);

            case 'tools/list':
                return $this->listTools();

            case 'tools/call':
                $name = $params['name'] ?? null;
                $arguments = $params['arguments'] ?? [];
                return $this->callTool($name, $arguments);

            case 'resources/list':
                return $this->listResources();

            case 'resources/read':
                $uri = $params['uri'] ?? null;
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
        $protocolVersion = $clientProtocolVersion ?: '2024-11-05';

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
        $tools = [];
        foreach ($this->tools as $name => $tool) {
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
            throw new Exception("Unknown tool: $name");
        }

        $tool = $this->tools[$name];
        return $tool->execute($arguments);
    }

    /**
     * List all available resources
     *
     * @return array
     */
    private function listResources(): array
    {
        $resources = [];
        foreach ($this->resources as $uri => $resource) {
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
        if (!isset($this->resources[$uri])) {
            throw new Exception("Unknown resource: $uri");
        }

        $resource = $this->resources[$uri];
        return $resource->read();
    }

    /**
     * Register all MCP tools
     */
    private function registerTools(): void
    {
        $toolClasses = [
            Tools\ApplicationInfoTool::class,
            Tools\DatabaseSchemaTool::class,
            Tools\ConfigAccessTool::class,
            Tools\RouteInspectorTool::class,
            Tools\ComponentInspectorTool::class,
        ];

        foreach ($toolClasses as $class) {
            $tool = new $class(['basePath' => $this->basePath]);
            $this->tools[$tool->getName()] = $tool;
        }
    }

    /**
     * Register MCP resources
     */
    private function registerResources(): void
    {
        $this->resources['guidelines://core'] = new Resources\GuidelinesResource([
            'basePath' => $this->basePath,
        ]);

        $this->resources['config://boost'] = new Resources\BoostConfigResource([
            'basePath' => $this->basePath,
        ]);
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
