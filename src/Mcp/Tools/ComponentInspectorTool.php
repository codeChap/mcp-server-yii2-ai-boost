<?php

namespace codechap\yii2boost\Mcp\Tools;

use Yii;

/**
 * Component Inspector Tool
 *
 * Provides application component introspection including:
 * - All registered components
 * - Component class names and configurations
 * - Bootstrap status
 * - Singleton vs new instance behavior
 */
class ComponentInspectorTool extends BaseTool
{
    public function getName()
    {
        return 'component_inspector';
    }

    public function getDescription()
    {
        return 'Inspect application components including their classes, configurations, and properties';
    }

    public function getInputSchema()
    {
        return [
            'type' => 'object',
            'properties' => [
                'component' => [
                    'type' => 'string',
                    'description' => 'Specific component to inspect (optional)',
                ],
                'include_config' => [
                    'type' => 'boolean',
                    'description' => 'Include full configuration (default: true)',
                ],
            ],
        ];
    }

    public function execute($arguments)
    {
        $component = $arguments['component'] ?? null;
        $includeConfig = isset($arguments['include_config']) ? $arguments['include_config'] : true;

        if ($component) {
            return $this->getComponentDetails($component, $includeConfig);
        }

        return $this->listComponents($includeConfig);
    }

    /**
     * List all components
     *
     * @param bool $includeConfig Include configuration details
     * @return array
     */
    private function listComponents($includeConfig = true)
    {
        $app = Yii::$app;
        $components = [];

        foreach ($app->getComponents() as $id => $component) {
            $components[$id] = $this->getComponentDetails($id, $includeConfig);
        }

        return ['components' => $components];
    }

    /**
     * Get details for a specific component
     *
     * @param string $id Component ID
     * @param bool $includeConfig Include configuration details
     * @return array
     * @throws \Exception
     */
    private function getComponentDetails($id, $includeConfig = true)
    {
        $app = Yii::$app;

        if (!$app->has($id)) {
            throw new \Exception("Component '$id' not found");
        }

        // Get the component definition (config)
        $componentDef = $app->getComponent($id, false);

        // Get the loaded instance (if available)
        $component = null;
        try {
            $component = $app->get($id);
        } catch (\Exception $e) {
            // Component couldn't be loaded
        }

        $details = [
            'id' => $id,
            'class' => $component ? get_class($component) : (is_array($componentDef) ? $componentDef['class'] ?? 'unknown' : 'unknown'),
            'is_loaded' => $component !== null,
        ];

        // Check if it's a singleton
        try {
            $isSingleton = $app->getSingleton($id) ? true : false;
            $details['is_singleton'] = $isSingleton;
        } catch (\Exception $e) {
            $details['is_singleton'] = false;
        }

        // Add configuration if requested
        if ($includeConfig && is_array($componentDef)) {
            $details['config'] = $this->sanitize($componentDef);
        }

        // Add public properties if component is loaded
        if ($component) {
            $details['properties'] = $this->getComponentProperties($component);
        }

        return $details;
    }

    /**
     * Get public properties of a component
     *
     * @param object $component Component instance
     * @return array
     */
    private function getComponentProperties($component)
    {
        $properties = [];

        try {
            $reflection = new \ReflectionClass($component);

            foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
                if (!$property->isStatic()) {
                    $name = $property->getName();
                    try {
                        $value = $property->getValue($component);

                        // Try to get a readable value
                        if (is_object($value)) {
                            $properties[$name] = get_class($value);
                        } elseif (is_array($value)) {
                            $properties[$name] = '[array with ' . count($value) . ' items]';
                        } else {
                            $properties[$name] = $value;
                        }
                    } catch (\Exception $e) {
                        $properties[$name] = '[unable to read]';
                    }
                }
            }
        } catch (\Exception $e) {
            // Cannot reflect component
        }

        return $properties;
    }
}
