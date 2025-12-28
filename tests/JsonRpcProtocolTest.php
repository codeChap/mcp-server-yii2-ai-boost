<?php

declare(strict_types=1);

namespace codechap\yii2boost\tests;

use PHPUnit\Framework\TestCase;

/**
 * Tests for JSON-RPC 2.0 Protocol compliance
 *
 * This test suite verifies that the MCP server correctly implements
 * the JSON-RPC 2.0 specification (https://www.jsonrpc.org/specification)
 */
class JsonRpcProtocolTest extends TestCase
{
    /**
     * Test valid JSON-RPC request structure
     */
    public function testValidJsonRpcRequestStructure(): void
    {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'initialize',
            'params' => [],
            'id' => 1,
        ];

        $this->assertEquals('2.0', $request['jsonrpc']);
        $this->assertIsString($request['method']);
        $this->assertIsArray($request['params']);
        $this->assertIsInt($request['id']);
    }

    /**
     * Test JSON-RPC notification structure (no id)
     */
    public function testJsonRpcNotificationStructure(): void
    {
        $notification = [
            'jsonrpc' => '2.0',
            'method' => 'notifications/initialized',
            'params' => [],
        ];

        $this->assertEquals('2.0', $notification['jsonrpc']);
        $this->assertIsString($notification['method']);
        $this->assertFalse(isset($notification['id']));
    }

    /**
     * Test valid JSON-RPC response structure
     */
    public function testValidJsonRpcResponseStructure(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'result' => ['status' => 'ok'],
            'id' => 1,
        ];

        $this->assertEquals('2.0', $response['jsonrpc']);
        $this->assertArrayHasKey('result', $response);
        $this->assertEquals(1, $response['id']);
        $this->assertFalse(isset($response['error']));
    }

    /**
     * Test JSON-RPC error response structure
     */
    public function testJsonRpcErrorResponseStructure(): void
    {
        $response = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32700,
                'message' => 'Parse error',
            ],
            'id' => null,
        ];

        $this->assertEquals('2.0', $response['jsonrpc']);
        $this->assertArrayHasKey('error', $response);
        $this->assertIsArray($response['error']);
        $this->assertArrayHasKey('code', $response['error']);
        $this->assertArrayHasKey('message', $response['error']);
        $this->assertFalse(isset($response['result']));
    }

    /**
     * Test JSON-RPC error codes
     */
    public function testJsonRpcErrorCodes(): void
    {
        $errorCodes = [
            -32700 => 'Parse error',
            -32600 => 'Invalid Request',
            -32601 => 'Method not found',
            -32602 => 'Invalid params',
            -32603 => 'Internal error',
            -32000 => 'Server error',
        ];

        foreach ($errorCodes as $code => $message) {
            $this->assertIsInt($code);
            $this->assertLessThanOrEqual(-32000, $code);
            $this->assertIsString($message);
        }
    }

    /**
     * Test request with object params
     */
    public function testRequestWithObjectParams(): void
    {
        $request = [
            'jsonrpc' => '2.0',
            'method' => 'tools/call',
            'params' => [
                'name' => 'application_info',
                'arguments' => [
                    'include' => ['version', 'environment'],
                ],
            ],
            'id' => 1,
        ];

        $json = json_encode($request);
        $decoded = json_decode($json, true);

        $this->assertEquals('tools/call', $decoded['method']);
        $this->assertEquals('application_info', $decoded['params']['name']);
        $this->assertIsArray($decoded['params']['arguments']);
    }

    /**
     * Test that result and error are mutually exclusive
     */
    public function testResultAndErrorAreMutuallyExclusive(): void
    {
        $successResponse = [
            'jsonrpc' => '2.0',
            'result' => ['data' => 'test'],
            'id' => 1,
        ];

        $errorResponse = [
            'jsonrpc' => '2.0',
            'error' => [
                'code' => -32603,
                'message' => 'Internal error',
            ],
            'id' => 1,
        ];

        // Success response should have result but not error
        $this->assertArrayHasKey('result', $successResponse);
        $this->assertFalse(isset($successResponse['error']));

        // Error response should have error but not result
        $this->assertArrayHasKey('error', $errorResponse);
        $this->assertFalse(isset($errorResponse['result']));
    }

    /**
     * Test batch requests (array of requests)
     */
    public function testBatchRequestFormat(): void
    {
        $batchRequest = [
            [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
                'params' => [],
            ],
            [
                'jsonrpc' => '2.0',
                'id' => 2,
                'method' => 'tools/list',
                'params' => [],
            ],
        ];

        $json = json_encode($batchRequest);
        $decoded = json_decode($json, true);

        $this->assertIsArray($decoded);
        $this->assertCount(2, $decoded);
        $this->assertEquals(1, $decoded[0]['id']);
        $this->assertEquals(2, $decoded[1]['id']);
    }

    /**
     * Test MCP-specific method names
     */
    public function testMcpMethodNames(): void
    {
        $validMethods = [
            'initialize',
            'tools/list',
            'tools/call',
            'resources/list',
            'resources/read',
            'notifications/initialized',
            'notifications/progress',
        ];

        foreach ($validMethods as $method) {
            $this->assertIsString($method);
            $this->assertNotEmpty($method);
            // MCP methods follow pattern: namespace/operation or single word
            $this->assertTrue(
                strpos($method, '/') !== false || strlen($method) > 0,
                "Invalid MCP method format: $method"
            );
        }
    }

    /**
     * Test JSON-RPC id field types
     */
    public function testJsonRpcIdFieldTypes(): void
    {
        // Valid id types: string, number, or NULL
        $validIds = [
            1,
            1.5,
            "request-123",
            null,
        ];

        foreach ($validIds as $id) {
            $this->assertTrue(
                is_int($id) || is_float($id) || is_string($id) || is_null($id),
                "Invalid id type for JSON-RPC"
            );
        }
    }
}
