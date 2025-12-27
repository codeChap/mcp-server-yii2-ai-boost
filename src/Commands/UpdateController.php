<?php

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
    public function actionIndex()
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
     * Update guidelines from remote repository
     *
     * This is a placeholder for now. In production, this would download
     * updated guidelines from a remote repository.
     */
    private function updateGuidelines()
    {
        $basePath = Yii::getAlias('@app');
        $guidelinesPath = $basePath . '/.ai/guidelines';

        if (!is_dir($guidelinesPath)) {
            throw new \Exception("Guidelines directory not found. Run 'php yii boost/install' first.");
        }

        // For now, just check that guidelines exist
        $coreGuidelinesPath = $guidelinesPath . '/core';
        if (is_dir($coreGuidelinesPath)) {
            $this->stdout("  ✓ Core guidelines directory exists\n", 32);
        }

        $this->stdout("  ✓ Guidelines checked\n", 32);
        $this->stdout("  (Remote download feature coming soon)\n", 36);
    }

    /**
     * Verify installation
     */
    private function verifyInstallation()
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
