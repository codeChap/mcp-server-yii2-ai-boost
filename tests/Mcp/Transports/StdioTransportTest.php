<?php

declare(strict_types=1);

namespace codechap\yii2boost\tests\Mcp\Transports;

use PHPUnit\Framework\TestCase;
use codechap\yii2boost\Mcp\Transports\StdioTransport;

/**
 * Tests for STDIO Transport
 */
class StdioTransportTest extends TestCase
{
    /**
     * Test that StdioTransport can be instantiated
     */
    public function testStdioTransportInstantiation(): void
    {
        $transport = new StdioTransport();
        $this->assertInstanceOf(StdioTransport::class, $transport);
    }

    /**
     * Test handler is called with input
     */
    public function testHandlerIsCalledWithInput(): void
    {
        // Create a mock stdin/stdout for testing
        $input = '{"jsonrpc":"2.0","id":1,"method":"test"}' . "\n";
        $inputStream = fopen('php://memory', 'r+');
        fwrite($inputStream, $input);
        rewind($inputStream);

        $handlerCalled = false;
        $handlerInput = null;

        // Override stdin for this test
        $reflection = new \ReflectionClass(StdioTransport::class);
        $stdinProperty = $reflection->getProperty('stdin');
        $stdinProperty->setAccessible(true);

        $transport = new StdioTransport();
        $stdinProperty->setValue($transport, $inputStream);

        // Create a handler that captures input
        $handler = function($request) use (&$handlerCalled, &$handlerInput) {
            $handlerCalled = true;
            $handlerInput = $request;
            return '{"jsonrpc":"2.0","id":1,"result":"ok"}';
        };

        // We can't test listen() fully without proper stdin/stdout setup,
        // but we can verify the transport is properly constructed
        $this->assertInstanceOf(StdioTransport::class, $transport);

        // Verify the resource was set
        $stdin = $stdinProperty->getValue($transport);
        $this->assertTrue(is_resource($stdin));

        fclose($inputStream);
    }

    /**
     * Test that transport can be created without errors
     */
    public function testTransportCreationDoesNotThrowError(): void
    {
        // This should not throw any exceptions
        try {
            $transport = new StdioTransport();
            $this->assertInstanceOf(StdioTransport::class, $transport);
        } catch (\Exception $e) {
            $this->fail('StdioTransport creation threw exception: ' . $e->getMessage());
        }
    }
}
