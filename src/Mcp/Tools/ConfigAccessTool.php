<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use Yii;
use yii\helpers\ArrayHelper;
use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

/**
 * Config Access Tool
 *
 * Provides safe access to application configuration including:
 * - Component configurations
 * - Module configurations
 * - Application parameters (params.php)
 * - Environment-specific configs
 *
 * Automatically sanitizes sensitive data (passwords, keys, tokens)
 */
class ConfigAccessTool extends BaseTool
{
    public function getName(): string
    {
        return 'config_access';
    }

    public function getDescription(): string
    {
        return 'Access application configuration including components, modules, and parameters (with sensitive data redaction)';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'component' => [
                    'type' => 'string',
                    'description' => 'Specific component to retrieve (optional)',
                ],
                'key' => [
                    'type' => 'string',
                    'description' => 'Specific config key to retrieve (optional)',
                ],
                'include' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'What to include: components, modules, params, all',
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $component = $arguments['component'] ?? null;
        $key = $arguments['key'] ?? null;
        $include = $arguments['include'] ?? ['components', 'modules', 'params'];

        $result = [];

        if ($component) {
            // Return specific component configuration
            $result = $this->getComponentConfig($component);
        } else {
            if (in_array('components', $include) || in_array('all', $include)) {
                $result['components'] = $this->getComponentsConfig();
            }

            if (in_array('modules', $include) || in_array('all', $include)) {
                $result['modules'] = $this->getModulesConfig();
            }

            if (in_array('params', $include) || in_array('all', $include)) {
                $result['params'] = $this->getParams();
            }
        }

        // Apply key filter if specified
        if ($key && isset($result[$key])) {
            return $result[$key];
        }

        return $result;
    }

    /**
     * Get all components configuration
     *
     * @return array
     */
    private function getComponentsConfig(): array
    {
        $app = Yii::$app;
        $components = [];

        foreach ($app->getComponents() as $id => $component) {
            $components[$id] = $this->getComponentConfig($id);
        }

        return $components;
    }

    /**
     * Get specific component configuration
     *
     * @param string $id Component ID
     * @return array
     * @throws \Exception
     */
    private function getComponentConfig(string $id): array
    {
        $app = Yii::$app;

        if (!$app->has($id)) {
            throw new \Exception("Component '$id' not found");
        }

        // Get component config from the components property
        $componentDef = $app->components[$id] ?? [];

        $componentClass = 'unknown';
        try {
            // Try to load the component and get its class
            $component = $app->get($id);
            $componentClass = get_class($component);
        } catch (\Exception $e) {
            // Component couldn't be loaded (e.g., web components in console context)
            // Fall back to getting class from config
            if (is_array($componentDef) && isset($componentDef['class'])) {
                $componentClass = $componentDef['class'];
            }
        }

        return [
            'id' => $id,
            'class' => $componentClass,
            'config' => $this->sanitize($componentDef),
        ];
    }

    /**
     * Get all modules configuration
     *
     * @return array
     */
    private function getModulesConfig(): array
    {
        $app = Yii::$app;
        $modules = [];

        foreach ($app->getModules() as $id => $module) {
            $modules[$id] = [
                'id' => $id,
                'class' => get_class($module),
                'basePath' => $module->basePath ?? null,
                'layout' => $module->layout ?? null,
            ];
        }

        return $modules;
    }

    /**
     * Get application parameters
     *
     * @return array
     */
    private function getParams(): array
    {
        return $this->sanitize(Yii::$app->params);
    }
}
