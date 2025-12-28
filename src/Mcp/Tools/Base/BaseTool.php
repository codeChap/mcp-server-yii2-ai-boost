<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools\Base;

use yii\base\Component;

/**
 * Base class for MCP Tools
 *
 * All MCP tools should extend this class and implement the required methods.
 */
abstract class BaseTool extends Component
{
    /**
     * @var string Base path to the Yii2 application
     */
    public $basePath;

    /**
     * Get the tool name
     *
     * @return string
     */
    abstract public function getName(): string;

    /**
     * Get the tool description
     *
     * @return string
     */
    abstract public function getDescription(): string;

    /**
     * Get the tool input schema (JSON Schema)
     *
     * @return array
     */
    abstract public function getInputSchema(): array;

    /**
     * Execute the tool with given arguments
     *
     * @param array $arguments Tool arguments
     * @return mixed Result data
     * @throws \Exception
     */
    abstract public function execute(array $arguments): mixed;

    /**
     * Sanitize output to remove sensitive data
     *
     * @param mixed $data Data to sanitize
     * @return mixed Sanitized data
     */
    protected function sanitize(mixed $data): mixed
    {
        // List of sensitive keys to filter
        $sensitiveKeys = [
            'password', 'secret', 'key', 'token', 'api_key', 'private_key',
            'auth_key', 'access_token', 'refresh_token', 'client_secret',
        ];

        if (is_array($data)) {
            $sanitized = [];
            foreach ($data as $key => $value) {
                // Only check string keys for sensitive patterns
                if (is_string($key)) {
                    $lowerKey = strtolower($key);

                    // Check if key contains sensitive pattern
                    $isSensitive = false;
                    foreach ($sensitiveKeys as $pattern) {
                        if (stripos($lowerKey, $pattern) !== false) {
                            $isSensitive = true;
                            break;
                        }
                    }

                    if ($isSensitive) {
                        $sanitized[$key] = '***REDACTED***';
                    } else {
                        $sanitized[$key] = $this->sanitize($value);
                    }
                } else {
                    // Non-string keys (integers, etc) are always safe
                    $sanitized[$key] = $this->sanitize($value);
                }
            }
            return $sanitized;
        } elseif (is_string($data) && !empty($data)) {
            // Don't sanitize regular strings
            return $data;
        }

        return $data;
    }

    /**
     * Get all database connections
     *
     * @return array Array of database names and connection info
     */
    protected function getDbConnections(): array
    {
        $connections = [];
        $app = \Yii::$app;

        // Main database connection
        if ($app->has('db')) {
            $db = $app->get('db');
            $connections['main'] = [
                'dsn' => $db->dsn,
                'driver' => $this->getDbDriver($db->dsn),
                'username' => $db->username,
            ];
        }

        // Additional named connections
        foreach ($app->get('components', []) as $name => $config) {
            if (is_array($config) && isset($config['class']) &&
                (stripos($config['class'], 'yii\db\Connection') !== false)) {
                if ($name !== 'db') {
                    $db = $app->get($name);
                    $connections[$name] = [
                        'dsn' => $db->dsn,
                        'driver' => $this->getDbDriver($db->dsn),
                        'username' => $db->username,
                    ];
                }
            }
        }

        return $connections;
    }

    /**
     * Extract database driver from DSN
     *
     * @param string $dsn Database DSN
     * @return string Driver name
     */
    protected function getDbDriver(string $dsn): string
    {
        $driver = explode(':', $dsn)[0] ?? 'unknown';
        return $driver;
    }
}
