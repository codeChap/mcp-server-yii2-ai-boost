<?php

declare(strict_types=1);

namespace codechap\yii2boost\Mcp\Tools;

use Yii;
use codechap\yii2boost\Mcp\Tools\Base\BaseTool;

/**
 * Application Information Tool
 *
 * Provides complete context about the Yii2 application including:
 * - Yii2 and PHP versions
 * - Application type and environment
 * - Installed modules and extensions
 * - Environment variables (sanitized)
 */
class ApplicationInfoTool extends BaseTool
{
    public function getName(): string
    {
        return 'application_info';
    }

    public function getDescription(): string
    {
        return 'Get comprehensive information about the Yii2 application including version, environment, modules, and extensions';
    }

    public function getInputSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'include' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
                    'description' => 'Specific info to include: version, environment, modules, extensions, all',
                ],
            ],
        ];
    }

    public function execute(array $arguments): mixed
    {
        $include = $arguments['include'] ?? ['version', 'environment', 'modules', 'extensions'];

        $result = [];

        if (in_array('version', $include) || in_array('all', $include)) {
            $result['version'] = $this->getVersionInfo();
        }

        if (in_array('environment', $include) || in_array('all', $include)) {
            $result['environment'] = $this->getEnvironmentInfo();
        }

        if (in_array('modules', $include) || in_array('all', $include)) {
            $result['modules'] = $this->getModulesInfo();
        }

        if (in_array('extensions', $include) || in_array('all', $include)) {
            $result['extensions'] = $this->getExtensionsInfo();
        }

        return $result;
    }

    /**
     * Get version information
     *
     * @return array
     */
    private function getVersionInfo(): array
    {
        return [
            'yii2_version' => Yii::getVersion(),
            'php_version' => phpversion(),
            'php_sapi' => php_sapi_name(),
        ];
    }

    /**
     * Get environment information
     *
     * @return array
     */
    private function getEnvironmentInfo(): array
    {
        $result = [
            'yii_env' => YII_ENV,
            'yii_debug' => YII_DEBUG ? true : false,
            'base_path' => Yii::getAlias('@app'),
            'runtime_path' => Yii::getAlias('@runtime'),
        ];

        // Try to get web path if it exists (web app only)
        try {
            $result['web_path'] = Yii::getAlias('@webroot');
        } catch (\Exception $e) {
            // @webroot doesn't exist (console app)
        }

        return $result;
    }

    /**
     * Get application modules
     *
     * @return array
     */
    private function getModulesInfo(): array
    {
        $modules = [];
        $app = Yii::$app;

        foreach ($app->getModules() as $id => $module) {
            $modules[$id] = [
                'class' => get_class($module),
                'basePath' => $module->basePath ?? null,
            ];
        }

        return $modules;
    }

    /**
     * Get installed extensions from composer.json
     *
     * @return array
     */
    private function getExtensionsInfo(): array
    {
        $extensions = [];
        $vendorDir = Yii::getAlias('@vendor');

        // Read installed.json from Composer
        $installedFile = $vendorDir . '/composer/installed.json';
        if (file_exists($installedFile)) {
            $installed = json_decode(file_get_contents($installedFile), true);

            // Handle both flat array and nested structure (composer 2.0+)
            $packages = isset($installed['packages']) ? $installed['packages'] : $installed;

            foreach ($packages as $package) {
                if (strpos($package['name'] ?? '', 'yiisoft/') === 0) {
                    $extensions[$package['name']] = [
                        'version' => $package['version'] ?? 'unknown',
                        'description' => $package['description'] ?? '',
                    ];
                }
            }
        }

        return $extensions;
    }
}
