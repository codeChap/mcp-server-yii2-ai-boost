<?php

declare(strict_types=1);

namespace codechap\yii2boost\tests\Mcp\Tools;

use codechap\yii2boost\Mcp\Tools\TinkerTool;

class TinkerToolTest extends ToolTestCase
{
    private TinkerTool $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tool = new TinkerTool([
            'basePath' => __DIR__ . '/../../fixtures/app',
        ]);
    }

    public function testGetName(): void
    {
        $this->assertSame('tinker', $this->tool->getName());
    }

    public function testGetDescription(): void
    {
        $this->assertNotEmpty($this->tool->getDescription());
    }

    public function testGetInputSchema(): void
    {
        $schema = $this->tool->getInputSchema();
        $this->assertSame('object', $schema['type']);
        $this->assertArrayHasKey('code', $schema['properties']);
        $this->assertArrayHasKey('timeout', $schema['properties']);
        $this->assertContains('code', $schema['required']);
    }

    public function testSimpleExpression(): void
    {
        $result = $this->tool->execute(['code' => '1 + 2']);

        $this->assertTrue($result['success']);
        $this->assertSame(3, $result['return_value']);
        $this->assertSame('integer', $result['type']);
        $this->assertArrayHasKey('duration_ms', $result);
    }

    public function testStringExpression(): void
    {
        $result = $this->tool->execute(['code' => "strtoupper('hello')"]);

        $this->assertTrue($result['success']);
        $this->assertSame('HELLO', $result['return_value']);
        $this->assertSame('string', $result['type']);
    }

    public function testArrayExpression(): void
    {
        $result = $this->tool->execute(['code' => "['a' => 1, 'b' => 2]"]);

        $this->assertTrue($result['success']);
        $this->assertSame(['a' => 1, 'b' => 2], $result['return_value']);
        $this->assertSame('array', $result['type']);
    }

    public function testYiiAppAvailable(): void
    {
        $result = $this->tool->execute(['code' => '\Yii::$app->id']);

        $this->assertTrue($result['success']);
        $this->assertIsString($result['return_value']);
        $this->assertNotEmpty($result['return_value']);
    }

    public function testDatabaseQuery(): void
    {
        $result = $this->tool->execute([
            'code' => '\Yii::$app->db->createCommand("SELECT COUNT(*) AS cnt FROM user")->queryScalar()',
        ]);

        $this->assertTrue($result['success']);
        // SQLite returns string '0' for scalar queries
        $this->assertEquals(0, $result['return_value']);
    }

    public function testOutputCapture(): void
    {
        $result = $this->tool->execute(['code' => 'echo "hello world"']);

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('hello world', $result['output']);
    }

    public function testReturnAndOutput(): void
    {
        $result = $this->tool->execute([
            'code' => 'echo "output"; return 42;',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(42, $result['return_value']);
        $this->assertStringContainsString('output', $result['output']);
    }

    public function testMultiStatementWithExplicitReturn(): void
    {
        $result = $this->tool->execute([
            'code' => '$x = 10; $y = 20; return $x + $y;',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(30, $result['return_value']);
    }

    public function testAssignmentBeforeReturnDoesNotShadowFinalReturn(): void
    {
        // Regression: previously the expression-wrap (`return <code>;`) would
        // parse `return $first = "wrong";` and return immediately, leaving the
        // explicit final `return` unreachable.
        $result = $this->tool->execute([
            'code' => '$first = "wrong"; $second = "right"; return ["value" => $second];',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(['value' => 'right'], $result['return_value']);
    }

    public function testCollectorPatternWithImplodeReturn(): void
    {
        // Regression: the real-world failure case — building a list and
        // returning it via implode at the end.
        $result = $this->tool->execute([
            'code' => '$out = []; $out[] = "a"; $out[] = "b"; return implode(",", $out);',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('a,b', $result['return_value']);
    }

    public function testReturnInsideClosureDoesNotBlockExpressionWrap(): void
    {
        // A `return` nested inside a closure body should NOT be treated as a
        // top-level return, so the bare expression still gets wrapped.
        $result = $this->tool->execute([
            'code' => '(function () { return 7; })()',
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(7, $result['return_value']);
    }

    public function testExceptionHandling(): void
    {
        $result = $this->tool->execute([
            'code' => 'throw new \RuntimeException("test error")',
        ]);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('test error', $result['error']);
        $this->assertSame('RuntimeException', $result['error_class']);
    }

    public function testEmptyCode(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('cannot be empty');

        $this->tool->execute(['code' => '']);
    }

    public function testDangerousExit(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('blocked function');

        $this->tool->execute(['code' => 'exit()']);
    }

    public function testDangerousShellExec(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('blocked function');

        $this->tool->execute(['code' => 'shell_exec("ls")']);
    }

    public function testDangerousDie(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('blocked function');

        $this->tool->execute(['code' => 'die("bye")']);
    }

    public function testDangerousSystem(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('blocked function');

        $this->tool->execute(['code' => 'system("whoami")']);
    }

    public function testSensitiveDataRedacted(): void
    {
        $result = $this->tool->execute([
            'code' => "['username' => 'admin', 'password' => 'secret123']",
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame('***REDACTED***', $result['return_value']['password']);
        $this->assertSame('admin', $result['return_value']['username']);
    }

    public function testSyntaxError(): void
    {
        $result = $this->tool->execute([
            'code' => 'this is not valid php code }{',
        ]);

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['error']);
    }

    public function testTimeoutParameter(): void
    {
        $result = $this->tool->execute([
            'code' => '1 + 1',
            'timeout' => 10,
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['return_value']);
    }

    public function testCodeMaxLengthExceeded(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('maximum length');

        $this->tool->execute(['code' => str_repeat('x', 10001)]);
    }
}
