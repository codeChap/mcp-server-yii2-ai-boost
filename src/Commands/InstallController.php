<?php

declare(strict_types=1);

namespace codechap\yii2boost\Commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\FileHelper;

/**
 * Install Command
 *
 * Installs and configures Yii2 AI Boost in the application.
 *
 * Usage:
 *   php yii boost/install
 */
class InstallController extends Controller
{
    /**
     * Install Yii2 AI Boost in the application
     *
     * @return int Exit code
     */
    public function actionIndex(): int
    {
        $this->stdout("┌───────────────────────────────────────────┐\n", 32);
        $this->stdout("│      Yii2 AI Boost Installation Wizard    │\n", 32);
        $this->stdout("└───────────────────────────────────────────┘\n\n", 32);

        try {
            // Step 1: Detect environment
            $this->stdout("[1/5] Detecting Environment\n", 33);
            $envInfo = $this->detectEnvironment();
            $this->outputEnvironmentInfo($envInfo);

            // Step 2: Create directories
            $this->stdout("\n[2/5] Creating Directories\n", 33);
            $this->createDirectories();

            // Step 3: Generate configuration files
            $this->stdout("\n[3/5] Generating Configuration Files\n", 33);
            $this->generateConfigFiles($envInfo);

            // Step 4: Download guidelines
            $this->stdout("\n[4/5] Downloading Guidelines\n", 33);
            $this->downloadGuidelines();

            // Step 5: Register autoload
            $this->stdout("\n[5/5] Registering Package\n", 33);
            $this->stdout("✓ Package auto-discovery enabled via Composer bootstrap\n", 32);

            // Success message
            $this->outputSuccessMessage($envInfo);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("✗ Installation failed: " . $e->getMessage() . "\n", 31);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Detect application environment
     *
     * @return array
     */
    private function detectEnvironment(): array
    {
        $app = Yii::$app;

        return [
            'yii_version' => Yii::getVersion(),
            'php_version' => phpversion(),
            'app_base_path' => $app->getBasePath(),
            'runtime_path' => Yii::getAlias('@runtime'),
            'yii_env' => YII_ENV,
            'yii_debug' => YII_DEBUG,
        ];
    }

    /**
     * Output environment detection results
     *
     * @param array $envInfo Environment information
     */
    private function outputEnvironmentInfo(array $envInfo): void
    {
        $this->stdout("  ✓ Yii2 version: {$envInfo['yii_version']}\n", 32);
        $this->stdout("  ✓ PHP version: {$envInfo['php_version']}\n", 32);
        $this->stdout("  ✓ Environment: {$envInfo['yii_env']}\n", 32);
        $this->stdout("  ✓ Debug mode: " . ($envInfo['yii_debug'] ? 'ON' : 'OFF') . "\n", 32);
    }

    /**
     * Create necessary directories
     *
     * @throws \Exception
     */
    private function createDirectories(): void
    {
        $basePath = Yii::getAlias('@app');

        $directories = [
            $basePath . '/.ai',
            $basePath . '/.ai/guidelines',
            $basePath . '/.ai/guidelines/core',
            $basePath . '/.ai/guidelines/ecosystem',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                FileHelper::createDirectory($dir);
                $this->stdout("  ✓ Created directory: $dir\n", 32);
            }
        }
    }

    /**
     * Generate configuration files
     *
     * @param array $envInfo Environment information
     * @throws \Exception
     */
    private function generateConfigFiles(array $envInfo): void
    {
        $basePath = Yii::getAlias('@app');

        // Generate .mcp.json with absolute paths for maximum compatibility with MCP clients
        $phpPath = PHP_BINARY;
        $yiiPath = $basePath . '/yii';

        $mcpConfig = [
            'mcpServers' => [
                'yii2-boost' => [
                    'command' => $phpPath,
                    'args' => [$yiiPath, 'boost/mcp'],
                    'env' => (object)[],
                ],
            ],
        ];

        file_put_contents(
            $basePath . '/.mcp.json',
            json_encode($mcpConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        $this->stdout("  ✓ Generated .mcp.json\n", 32);

        // Generate boost.json
        $boostConfig = [
            'version' => '1.0.0',
            'yii2_version' => $envInfo['yii_version'],
            'php_version' => $envInfo['php_version'],
            'environment' => $envInfo['yii_env'],
            'debug' => $envInfo['yii_debug'],
            'tools' => [
                'application_info',
                'database_schema',
                'config_access',
                'route_inspector',
                'component_inspector',
            ],
            'guidelines' => [
                'core' => '2.0.45',
            ],
        ];

        file_put_contents(
            $basePath . '/boost.json',
            json_encode($boostConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
        $this->stdout("  ✓ Generated boost.json\n", 32);

        // Add .mcp.json to .gitignore
        $this->addToGitignore($basePath, '.mcp.json');
        $this->stdout("  ✓ Added .mcp.json to .gitignore\n", 32);
    }

    /**
     * Add entry to .gitignore
     *
     * @param string $basePath Application base path
     * @param string $entry Entry to add
     */
    private function addToGitignore(string $basePath, string $entry): void
    {
        $gitignore = $basePath . '/.gitignore';

        if (file_exists($gitignore)) {
            $content = file_get_contents($gitignore);
            if (stripos($content, $entry) === false) {
                file_put_contents($gitignore, "\n$entry\n", FILE_APPEND);
            }
        } else {
            file_put_contents($gitignore, "$entry\n");
        }
    }

    /**
     * Download guidelines from remote repository
     *
     * This is a placeholder for now. In production, this would download
     * guidelines from a remote repository.
     */
    private function downloadGuidelines(): void
    {
        $basePath = Yii::getAlias('@app');
        $guidelinesPath = $basePath . '/.ai/guidelines/core';

        // For now, create an empty placeholder file
        // This will be populated with actual guidelines
        $placeholderFile = $guidelinesPath . '/yii2-2.0.45.md';

        if (!file_exists($placeholderFile)) {
            file_put_contents($placeholderFile, "# Yii2 Framework Guidelines\n\n[Guidelines will be downloaded in a future version]\n");
            $this->stdout("  ✓ Created guidelines placeholder\n", 32);
        }
    }

    /**
     * Output success message
     *
     * @param array $envInfo Environment information
     */
    private function outputSuccessMessage(array $envInfo): void
    {
        $this->stdout("\n", 0);
        $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n", 32);
        $this->stdout("Installation Complete!\n", 32);
        $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n", 32);

        $this->stdout("Next steps:\n", 36);
        $this->stdout("  1. Add Yii2 guidelines to your CLAUDE.md file:\n", 0);
        $this->stdout("     @include .ai/guidelines/core/yii2-2.0.45.md\n\n", 37);
        $this->stdout("  2. (Optional) Add ecosystem guidelines:\n", 0);
        $this->stdout("     See .ai/guidelines/ecosystem/ for additional guidelines\n\n", 37);
        $this->stdout("  3. Test MCP server: php yii boost/mcp\n", 0);
        $this->stdout("  4. View configuration: php yii boost/info\n\n", 0);

        $this->stdout("Configuration files created:\n", 36);
        $this->stdout("  • .mcp.json (IDE configuration)\n", 0);
        $this->stdout("  • boost.json (package configuration)\n", 0);
        $this->stdout("  • .ai/guidelines/ (framework and ecosystem guidelines)\n\n", 0);

        $this->stdout("MCP Server command:\n", 36);
        $this->stdout("  php yii boost/mcp\n\n", 37);
    }
}
