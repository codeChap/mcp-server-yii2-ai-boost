<?php

declare(strict_types=1);

namespace codechap\yii2boost\Commands;

use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Boost Command Controller
 *
 * Main controller that delegates to specific boost command handlers.
 * This controller is automatically registered by Bootstrap.php when
 * the package is installed via Composer.
 *
 * Available commands:
 *   php yii boost                (display info - default action)
 *   php yii boost/install        (run installation wizard)
 *   php yii boost/mcp            (start MCP server)
 *   php yii boost/info           (display package information)
 *   php yii boost/update         (update guidelines)
 */
class BoostController extends Controller
{
    /**
     * Display Yii2 AI Boost information (default action)
     *
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout("Yii2 AI Boost CLI\n", 36);
        $this->stdout("Available commands:\n");
        $this->stdout("  yii boost/install    - Initialize directories and config\n");
        $this->stdout("  yii boost/mcp        - Run the MCP server (stdio mode)\n");
        $this->stdout("  yii boost/info       - Show application info\n");
        $this->stdout("  yii boost/sync-rules - Sync AI guidelines to editor rules\n");
        $this->stdout("  yii boost/update     - Update guidelines\n");
        return ExitCode::OK;
    }

    /**
     * Run boost install wizard
     *
     * @return int
     */
    public function actionInstall(): int
    {
        $controller = new InstallController('boost/install', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Start MCP server
     *
     * @return int
     */
    public function actionMcp(): int
    {
        $controller = new McpController('boost/mcp', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Display Yii2 AI Boost information
     *
     * @return int
     */
    public function actionInfo(): int
    {
        $controller = new InfoController('boost/info', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Sync AI guidelines to editor rules
     *
     * @return int
     */
    public function actionSyncRules(): int
    {
        $controller = new SyncRulesController('boost/sync-rules', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Update Yii2 AI Boost components
     *
     * @return int
     */
    public function actionUpdate(): int
    {
        $controller = new UpdateController('boost/update', \Yii::$app);
        return $controller->runAction('index');
    }
}
