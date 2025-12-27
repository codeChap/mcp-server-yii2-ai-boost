<?php

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
 *   php yii boost/install
 *   php yii boost/mcp
 *   php yii boost/info
 *   php yii boost/update
 */
class BoostController extends Controller
{
    /**
     * Run boost install wizard
     *
     * @return int
     */
    public function actionInstall()
    {
        $controller = new InstallController('boost/install', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Start MCP server
     *
     * @return int
     */
    public function actionMcp()
    {
        $controller = new McpController('boost/mcp', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Display Yii2 AI Boost information
     *
     * @return int
     */
    public function actionInfo()
    {
        $controller = new InfoController('boost/info', \Yii::$app);
        return $controller->runAction('index');
    }

    /**
     * Update Yii2 AI Boost components
     *
     * @return int
     */
    public function actionUpdate()
    {
        $controller = new UpdateController('boost/update', \Yii::$app);
        return $controller->runAction('index');
    }
}
