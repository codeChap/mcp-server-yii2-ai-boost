<?php

declare(strict_types=1);

namespace codechap\yii2boost\Commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Update Command
 *
 * Updates Yii2 AI Boost components including guidelines.
 *
 * Usage:
 *   php yii boost/update
 */
class UpdateController extends Controller
{
    /**
     * Update Yii2 AI Boost components
     *
     * @return int Exit code
     */
    public function actionIndex(): int
    {
        $this->stdout("┌───────────────────────────────────────────┐\n", 32);
        $this->stdout("│   Yii2 AI Boost - Update                  │\n", 32);
        $this->stdout("└───────────────────────────────────────────┘\n\n", 32);

        try {
            $this->stdout("[1/2] Updating Guidelines\n", 33);
            $this->updateGuidelines();

            $this->stdout("\n[2/2] Verifying Installation\n", 33);
            $this->verifyInstallation();

            $this->stdout("\n", 0);
            $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n", 32);
            $this->stdout("Update Complete!\n", 32);
            $this->stdout("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n", 32);

            return ExitCode::OK;
        } catch (\Exception $e) {
            $this->stderr("✗ Update failed: " . $e->getMessage() . "\n", 31);
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Update guidelines from package source
     */
    private function updateGuidelines(): void
    {
        $appPath = Yii::getAlias('@app');
        $targetPath = $appPath . '/.ai/guidelines';
        
        // Locate source directory relative to this file
        // src/Commands/UpdateController.php -> .ai/guidelines
        $packageRoot = dirname(__DIR__, 2);
        $sourcePath = $packageRoot . '/.ai/guidelines';

        if (!is_dir($sourcePath)) {
            $this->stdout("  ! Package source guidelines not found at: $sourcePath\n", 33);
            return;
        }

        $this->stdout("  Copying guidelines from package...\n", 0);
        
        try {
            \yii\helpers\FileHelper::copyDirectory($sourcePath, $targetPath, [
                'dirMode' => 0755,
                'fileMode' => 0644,
            ]);
            $this->stdout("  ✓ Guidelines updated successfully\n", 32);
        } catch (\Exception $e) {
            throw new \Exception("Failed to copy guidelines: " . $e->getMessage());
        }
    }

    /**
     * Verify installation
     */
    private function verifyInstallation(): void
    {
        $basePath = Yii::getAlias('@app');

        $files = [
            'boost.json',
            'CLAUDE.md',
            '.ai/guidelines',
        ];

        foreach ($files as $file) {
            $path = $basePath . '/' . $file;
            if (file_exists($path)) {
                $this->stdout("  ✓ $file exists\n", 32);
            } else {
                $this->stdout("  ✗ $file missing\n", 31);
            }
        }
    }
}
