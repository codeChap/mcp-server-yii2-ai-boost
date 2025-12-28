<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use Yii;
use yii\web\UrlRule;
use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

/**
 * Route Inspector Tool
 *
 * Provides complete route mapping including:
 * - URL rules from urlManager
 * - Route → Controller/Action mappings
 * - Module routes with prefixes
 * - RESTful API routes
 * - Default routes
 */
class RouteInspectorTool extends BaseTool
{
    public function getName(): string
    {
        return 'route_inspector';
    }

    public function getDescription(): string
    {
        return 'Inspect application routes and URL rules including module routes and REST endpoints';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'module' => [
                    'type' => 'string',
                    'description' => 'Specific module to inspect (optional)',
                ],
                'include_patterns' => [
                    'type' => 'boolean',
                    'description' => 'Include regex patterns in routes',
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $module = $arguments['module'] ?? null;
        $includePatterns = $arguments['include_patterns'] ?? false;

        if ($module) {
            return $this->getModuleRoutes($module, $includePatterns);
        }

        return [
            'url_rules' => $this->getUrlRules($includePatterns),
            'modules' => $this->getModuleRoutes(null, $includePatterns),
        ];
    }

    /**
     * Get all URL rules from urlManager
     *
     * @param bool $includePatterns Include regex patterns
     * @return array
     */
    private function getUrlRules(bool $includePatterns = false): array
    {
        $app = Yii::$app;
        if (!$app->has('urlManager')) {
            return [];
        }

        $urlManager = $app->get('urlManager');
        $rules = [];

        foreach ($urlManager->rules as $rule) {
            if ($rule instanceof UrlRule) {
                $ruleData = [
                    'pattern' => $rule->name,
                    'route' => $rule->route,
                ];

                if (!empty($rule->verb)) {
                    $ruleData['verb'] = $rule->verb;
                }

                if ($includePatterns && !empty($rule->pattern)) {
                    $ruleData['regex_pattern'] = $rule->pattern;
                }

                $rules[] = $ruleData;
            } elseif (is_array($rule)) {
                // Array-style rule
                $rules[] = [
                    'pattern' => $rule[0] ?? null,
                    'route' => $rule[1] ?? null,
                ];
            }
        }

        return $rules;
    }

    /**
     * Get routes for a specific module or all module routes
     *
     * @param string|null $moduleName Module name
     * @param bool $includePatterns Include regex patterns
     * @return array
     * @throws \Exception
     */
    private function getModuleRoutes(?string $moduleName = null, bool $includePatterns = false): array
    {
        $app = Yii::$app;
        $result = [];

        if ($moduleName) {
            if (!$app->hasModule($moduleName)) {
                throw new \Exception("Module '$moduleName' not found");
            }

            $module = $app->getModule($moduleName);
            return [
                'module' => $moduleName,
                'routes' => $this->scanModuleControllers($module, $moduleName),
            ];
        }

        // Get routes for all modules
        foreach ($app->getModules() as $id => $module) {
            $result[$id] = $this->scanModuleControllers($module, $id);
        }

        return $result;
    }

    /**
     * Scan module directory for controllers and actions
     *
     * @param object $module Module instance
     * @param string $moduleId Module ID
     * @return array
     */
    private function scanModuleControllers(object $module, string $moduleId): array
    {
        $controllersPath = $module->basePath . '/controllers';
        $routes = [];

        if (!is_dir($controllersPath)) {
            return $routes;
        }

        $iterator = new \DirectoryIterator($controllersPath);

        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'php') {
                continue;
            }

            $controllerName = substr($file->getFilename(), 0, -4); // Remove .php

            // Convert ControllerName to controller-name route
            $routeName = $this->camelCaseToKebabCase(
                substr($controllerName, 0, -10) // Remove 'Controller' suffix
            );

            $routes[$routeName] = [
                'controller' => $routeName,
                'module' => $moduleId,
                'full_path' => $moduleId . '/' . $routeName,
            ];
        }

        return $routes;
    }

    /**
     * Convert CamelCase to kebab-case
     *
     * @param string $string String to convert
     * @return string
     */
    private function camelCaseToKebabCase(string $string): string
    {
        return strtolower(preg_replace(
            '/([a-z0-9]|(?<=[a-z])[A-Z]|(?<=[A-Z])[A-Z](?=[a-z]))([A-Z])/s',
            '$1-$2',
            $string
        ));
    }
}
