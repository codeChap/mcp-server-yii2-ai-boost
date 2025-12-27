<?php

declare(strict_types=1);

namespace codechap\yii2boost;

use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;

/**
 * Bootstrap class for auto-registering Yii2 AI Boost commands
 *
 * This class is automatically loaded by Composer via the extra.bootstrap configuration.
 * It registers MCP-related console commands when the application is a console application.
 *
 * Provides the following commands (no extra application controller needed):
 * - php yii boost/install  (InstallController::actionInstall)
 * - php yii boost/mcp      (McpController::actionIndex)
 * - php yii boost/info     (InfoController::actionIndex)
 * - php yii boost/update   (UpdateController::actionIndex)
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method called on application initialization
     *
     * @param \yii\base\Application $app The application instance
     */
    public function bootstrap($app): void
    {
        // Only register commands in console applications
        if ($app instanceof ConsoleApplication) {
            // Register boost command controller (automatically handles all boost/* actions)
            $app->controllerMap['boost'] = [
                'class' => Commands\BoostController::class,
            ];
        }
    }
}
