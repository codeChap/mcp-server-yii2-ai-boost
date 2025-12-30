<?php

declare(strict_types=1);

namespace codechap\yii2boost\Commands;

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\FileHelper;

/**
 * Syncs AI guidelines to editor configurations.
 */
class SyncRulesController extends Controller
{
    /**
     * @var string Path to the .ai/guidelines directory
     */
    private $guidelinesPath;

    /**
     * @var string Path to the .cursor/rules directory
     */
    private $cursorRulesPath;

    /**
     * @var string Path to the .rules file (for Zed editor)
     */
    private $zedRulesPath;

    public function init()
    {
        parent::init();
        $this->guidelinesPath = \Yii::getAlias('@app/.ai/guidelines');
        $this->cursorRulesPath = \Yii::getAlias('@app/.cursor/rules');
        $this->zedRulesPath = \Yii::getAlias('@app/.rules');
    }

    /**
     * Syncs the guidelines to .cursor/rules/yii2-boost.mdc and .rules (Zed)
     */
    public function actionIndex(): int
    {
        $this->stdout("Syncing Yii2 Boost guidelines to Editor rules...\n", 36);

        if (!is_dir($this->guidelinesPath)) {
            $this->stderr("Error: Guidelines directory not found at {$this->guidelinesPath}\n", 31);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $content = $this->generateMdcContent();

        // Show preview before asking for confirmation
        $this->stdout("\nProposed rules content preview (first 800 characters):\n", 33);
        $this->stdout("────────────────────────────────────────────────────────\n", 33);
        $this->stdout(substr($content, 0, 800) . (strlen($content) > 800 ? "\n... (content continues) ...\n" : ""), 0);
        $this->stdout("────────────────────────────────────────────────────────\n", 33);

        if (!$this->confirm("Proceed with syncing rules to editor configurations?")) {
            $this->stdout("  - Sync cancelled\n", 33);
            return ExitCode::OK;
        }

        $this->stdout("\n");

        // 1. Sync Cursor Rules
        if (!is_dir($this->cursorRulesPath)) {
            FileHelper::createDirectory($this->cursorRulesPath);
            $this->stdout("  ✓ Created .cursor/rules directory\n", 32);
        }

        $cursorOutputFile = $this->cursorRulesPath . '/yii2-boost.mdc';
        file_put_contents($cursorOutputFile, $content);
        $this->stdout("  ✓ Synced Cursor rules to {$cursorOutputFile}\n", 32);

        // 2. Sync Zed Rules
        file_put_contents($this->zedRulesPath, $content);
        $this->stdout("  ✓ Synced Zed rules to {$this->zedRulesPath}\n", 32);

        return ExitCode::OK;
    }

    /**
     * Update file with user confirmation
     *
     * @param string $filePath File path to update
     * @param string $newContent New file content
     * @param string $description Description of the file
     * @return bool Whether the file was updated
     */
    private function updateFileWithConfirmation(string $filePath, string $newContent, string $description): bool
    {
        $exists = file_exists($filePath);

        if ($exists) {
            $oldContent = file_get_contents($filePath);
            if ($oldContent === $newContent) {
                $this->stdout("  ✓ $description already up-to-date\n", 32);
                return false;
            }
        }

        $this->stdout("\nProposed update to $description:\n", 33);
        $this->stdout("────────────────────────────────────\n", 33);
        $this->stdout(substr($newContent, 0, 500) . (strlen($newContent) > 500 ? "\n... (truncated) ...\n" : ""), 0);
        $this->stdout("────────────────────────────────────\n", 33);

        if ($this->confirm("Apply this update to $description?")) {
            file_put_contents($filePath, $newContent);
            $this->stdout("  ✓ Updated $description\n", 32);
            return true;
        } else {
            $this->stdout("  - Skipped updating $description\n", 33);
            return false;
        }
    }

    /**
     * Generates the content for the MDC file.
     *
     * It prioritizes the Core markdown guide and then appends key structural references.
     */
    private function generateMdcContent(): string
    {
        $mdc = "# Yii2 Framework Guidelines (Boost)\n\n";
        $mdc .= "You are an expert Yii2 developer working in a Yii 2.0.45 application.\n";
        $mdc .= "Follow these strict guidelines and structural references.\n\n";

        // 1. Core Guide (High Priority)
        $coreFile = $this->guidelinesPath . '/core/yii2-2.0.45.md';
        if (file_exists($coreFile)) {
            $mdc .= "## Core Framework Guide\n\n";
            $mdc .= file_get_contents($coreFile) . "\n\n";
        }

        // 2. Key Structural References
        $references = [
            'Controller' => 'http_web/yii-web-controller.md',
            'ActiveRecord' => 'database/yii-active-record.md',
            'Migration' => 'database/yii-migration.md',
            'View' => 'views_templating/yii-view.md',
        ];

        foreach ($references as $name => $relPath) {
            $file = $this->guidelinesPath . '/' . $relPath;
            if (file_exists($file)) {
                $mdc .= "## Reference: {$name} Structure\n\n";
                $mdc .= "Use this structure for {$name} classes:\n\n";
                $mdc .= file_get_contents($file) . "\n\n";
            }
        }

        return $mdc;
    }
}
