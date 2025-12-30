```php
<?php

/**
 * AI Guideline: Yii 2.0 Third Party & Extensions
 * 
 * This file serves as a reference for common official extensions/modules.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-debug-module
 * @see https://www.yiiframework.com/doc/api/2.0/yii-gii-module
 */

namespace yii\debug;

/**
 * The Debug Module provides a debug toolbar and debugger.
 */
class Module extends \yii\base\Module
{
    /**
     * @var array list of allowed IPs.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    /**
     * @var array|Panel[] list of debug panels.
     */
    public $panels = [];
}

namespace yii\gii;

/**
 * The Gii Module provides a web-based code generator.
 */
class Module extends \yii\base\Module
{
    /**
     * @var array list of allowed IPs.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    /**
     * @var array|Generator[] list of code generators.
     */
    public $generators = [];
}

/**
 * Configuration Example for config/web.php (dev environment)
 * 
 * ```php
 * if (YII_ENV_DEV) {
 *     $config['bootstrap'][] = 'debug';
 *     $config['modules']['debug'] = [
 *         'class' => 'yii\debug\Module',
 *     ];
 * 
 *     $config['bootstrap'][] = 'gii';
 *     $config['modules']['gii'] = [
 *         'class' => 'yii\gii\Module',
 *         'allowedIPs' => ['127.0.0.1', '::1'],
 *     ];
 * }
 * ```
 */
class ConfigurationExample {}
\n```
