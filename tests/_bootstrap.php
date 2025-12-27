<?php

/**
 * Test bootstrap file
 */

// Define test environment
defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

// Require Composer autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Require Yii
require_once dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';
