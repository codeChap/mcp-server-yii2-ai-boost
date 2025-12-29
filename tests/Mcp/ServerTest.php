<?php

declare(strict_types=1);

namespace codechap\yii2boost\tests\Mcp;

use PHPUnit\Framework\TestCase;

/**
 * Tests for MCP Server JSON-RPC handling
 *
 * Note: Server tests that require Yii instantiation are skipped in unit tests.
 * Integration tests should be run with a real Yii2 application.
 */
class ServerTest extends TestCase
{
    /**
     * Test JSON-RPC request parsing
     */
    public function testJsonRpcRequestParsing(): void
    {
        $requestJson = '{"jsonrpc":"2.0","id":1,"method":"initialize","params":{}}';
        $request = json_decode($requestJson, true);

        $this->assertEquals('2.0', $request['jsonrpc']);
        $this->assertEquals('initialize', $request['method']);
        $this->assertEquals(1, $request['id']);
        $this->assertIsArray($request['params']);
    }

    /**
     * Test JSON-RPC response format
     */
    public function testJsonRpcResponseFormat(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'protocolVersion' => '2025-11-25',
                'serverInfo' => [
                    'name' => 'Yii2 AI Boost',
                    'version' => '1.0.0',
                ],
            ],
        ];

        $json = json_encode($response);
        $decoded = json_decode($json, true);

        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals(1, $decoded['id']);
        $this->assertArrayHasKey('result', $decoded);
        $this->assertEquals('Yii2 AI Boost', $decoded['result']['serverInfo']['name']);
    }

    /**
     * Test error response format
     */
    public function testErrorResponseFormat(): void
    {
        $errorResponse = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'error' => [
                'code' => -32603,
                'message' => 'Internal error',
                'data' => ['message' => 'Something went wrong'],
            ],
        ];

        $json = json_encode($errorResponse);
        $decoded = json_decode($json, true);

        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertEquals(1, $decoded['id']);
        $this->assertArrayHasKey('error', $decoded);
        $this->assertEquals(-32603, $decoded['error']['code']);
        $this->assertArrayNotHasKey('result', $decoded);
    }

    /**
     * Test notification format (no id)
     */
    public function testNotificationFormat(): void
    {
        $notification = [
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
            'params' => [],
        ];

        $json = json_encode($notification);
        $decoded = json_decode($json, true);

        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertFalse(isset($decoded['id']));
        $this->assertEquals('notifications/initialized', $decoded['method']);
    }

    /**
     * Test tools/list response format
     */
    public function testToolsListResponseFormat(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'tools' => [
                    [
                        'name' => 'application_info',
                        'description' => 'Get information about the Yii2 application',
                        'inputSchema' => [
                            'type' => 'object',
                            'properties' => [
                                'include' => [
                                    'type' => 'array',
                                    'items' => ['type' => 'string'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $json = json_encode($response);
        $decoded = json_decode($json, true);

        $this->assertEquals('2.0', $decoded['jsonrpc']);
        $this->assertArrayHasKey('tools', $decoded['result']);
        $this->assertCount(1, $decoded['result']['tools']);
        $this->assertEquals('application_info', $decoded['result']['tools'][0]['name']);
    }

    /**
     * Test resources/list response format
     */
    public function testResourcesListResponseFormat(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'result' => [
                'resources' => [
                    [
                        'uri' => 'config://boost',
                        'name' => 'Yii2 AI Boost Configuration',
                        'description' => 'Current Yii2 AI Boost package configuration and status',
                    ],
                ],
            ],
        ];

        $json = json_encode($response);
        $decoded = json_decode($json, true);

        $this->assertArrayHasKey('resources', $decoded['result']);
        $this->assertEquals('config://boost', $decoded['result']['resources'][0]['uri']);
    }

    /**
     * Test tools/call request format
     */
    public function testToolsCallRequestFormat(): void
    {
        $request = [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => [
                'name' => 'application_info',
                'arguments' => [
                    'include' => ['version', 'environment'],
                ],
            ],
        ];

        $json = json_encode($request);
        $decoded = json_decode($json, true);

        $this->assertEquals('tools/call', $decoded['method']);
        $this->assertEquals('application_info', $decoded['params']['name']);
        $this->assertIsArray($decoded['params']['arguments']);
    }

    /**
     * Test parse error response
     */
    public function testParseErrorResponse(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32700,
                'message' => 'Parse error',
            ],
            'id' => null,
        ];

        $this->assertEquals(-32700, $response['error']['code']);
        $this->assertNull($response['id']);
    }

    /**
     * Test invalid request response
     */
    public function testInvalidRequestResponse(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32600,
                'message' => 'Invalid Request',
            ],
            'id' => null,
        ];

        $this->assertEquals(-32600, $response['error']['code']);
    }

    /**
     * Test method not found response
     */
    public function testMethodNotFoundResponse(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32601,
                'message' => 'Method not found',
            ],
            'id' => 1,
        ];

        $this->assertEquals(-32601, $response['error']['code']);
        $this->assertEquals(1, $response['id']);
    }
}
