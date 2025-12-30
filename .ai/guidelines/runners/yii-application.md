```php
<?php

/**
 * AI Guideline: Yii 2.0 Application Runners
 * 
 * This file serves as a reference for Application classes in Yii 2.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-web-application
 * @see https://www.yiiframework.com/doc/api/2.0/yii-console-application
 */

namespace yii\web;

use yii\base\Application as BaseApplication;

/**
 * Web Application
 */
class Application extends BaseApplication
{
    /**
     * @var string the default route of this application. Defaults to 'site'.
     */
    public $defaultRoute = 'site';

    /**
     * @var User|array|string the user component or its configuration.
     */
    public $user;

    /**
     * @var Session|array|string the session component or its configuration.
     */
    public $session;

    /**
     * Handles the specified request.
     * 
     * @param Request $request the request to be handled
     * @return Response the resulting response
     */
    public function handleRequest($request)
    {
        return new Response();
    }
}

namespace yii\console;

use yii\base\Application as BaseApplication;

/**
 * Console Application
 */
class Application extends BaseApplication
{
    /**
     * @var bool whether to enable the command line to read arguments.
     */
    public $enableCoreCommands = true;

    /**
     * Runs the console application.
     * 
     * @return int exit status
     */
    public function run()
    {
        return 0;
    }
}
\n```
