<?php

namespace codechap\yii2boost\Commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Info Command
 *
 * Displays information about Yii2 AI Boost installation and configuration.
 *
 * Usage:
 *   php yii boost/info
 */
class InfoController extends Controller
{
    /**
     * Display Yii2 AI Boost information
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        $this->stdout("╔════════════════════════════════════════╗\n", 36);
        $this->stdout("║    Yii2 AI Boost - Information         ║\n", 36);
        $this->stdout("╚════════════════════════════════════════╝\n\n", 36);

        try {
            $this->displayPackageInfo();
            $this->displayConfigStatus();
            $this->displayTools();
            $this->displayGuidelines();

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("✗ Error: " . $e->getMessage() . "\n", 31);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Display package information
     */
    private function displayPackageInfo()
    {
        $this->stdout("Package Information\n", 33);
        $this->stdout("─────────────────────────────────────────\n", 33);

        $basePath = Yii::getAlias('@app');
        $boostConfigFile = $basePath . '/boost.json';

        if (file_exists($boostConfigFile)) {
            $config = json_decode(file_get_contents($boostConfigFile), true);
            $this->stdout("  Version: " . ($config['version'] ?? 'unknown') . "\n", 32);
            $this->stdout("  Yii2 Version: " . ($config['yii2_version'] ?? 'unknown') . "\n", 32);
            $this->stdout("  Environment: " . ($config['environment'] ?? 'unknown') . "\n", 32);
        } else {
            $this->stdout("  ✗ boost.json not found (not installed?)\n", 31);
        }

        $this->stdout("\n", 0);
    }

    /**
     * Display configuration status
     */
    private function displayConfigStatus()
    {
        $basePath = Yii::getAlias('@app');

        $this->stdout("Configuration Status\n", 33);
        $this->stdout("─────────────────────────────────────────\n", 33);

        $files = [
            '.mcp.json' => 'MCP server configuration',
            'boost.json' => 'Package configuration',
            'CLAUDE.md' => 'Application guidelines',
            '.ai/guidelines' => 'Guidelines directory',
        ];

        foreach ($files as $file => $description) {
            $path = $basePath . '/' . $file;
            if (file_exists($path)) {
                $this->stdout("  ✓ $description\n", 32);
            } else {
                $this->stdout("  ✗ $description (missing)\n", 31);
            }
        }

        $this->stdout("\n", 0);
    }

    /**
     * Display available tools
     */
    private function displayTools()
    {
        $this->stdout("Available Tools\n", 33);
        $this->stdout("─────────────────────────────────────────\n", 33);

        $tools = [
            'application_info' => 'Get application version, environment, modules, extensions',
            'database_schema' => 'Inspect database tables, columns, indexes, and Active Record models',
            'config_access' => 'Access application configuration and parameters',
            'route_inspector' => 'Inspect application routes and URL rules',
            'component_inspector' => 'Inspect application components and their properties',
        ];

        foreach ($tools as $name => $description) {
            $this->stdout("  • $name\n", 36);
            $this->stdout("    $description\n", 0);
        }

        $this->stdout("\nTotal: " . count($tools) . " tools available\n\n", 32);
    }

    /**
     * Display guidelines status
     */
    private function displayGuidelines()
    {
        $basePath = Yii::getAlias('@app');
        $guidelinesPath = $basePath . '/.ai/guidelines';

        $this->stdout("Guidelines\n", 33);
        $this->stdout("─────────────────────────────────────────\n", 33);

        if (!is_dir($guidelinesPath)) {
            $this->stdout("  ✗ Guidelines directory not found\n", 31);
            return;
        }

        $coreGuidelinesPath = $guidelinesPath . '/core';
        if (is_dir($coreGuidelinesPath)) {
            $files = glob($coreGuidelinesPath . '/*.md');
            foreach ($files as $file) {
                $this->stdout("  ✓ " . basename($file) . "\n", 32);
            }
        }

        if (is_dir($guidelinesPath . '/ecosystem')) {
            $files = glob($guidelinesPath . '/ecosystem/*.md');
            foreach ($files as $file) {
                $this->stdout("  ✓ " . basename($file) . "\n", 32);
            }
        }

        $this->stdout("\n", 0);
    }
}
