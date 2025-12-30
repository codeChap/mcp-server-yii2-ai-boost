```php
<?php

/**
 * AI Guideline: Yii 2.0 Logging Structure
 * 
 * This file serves as a reference for using Logging in Yii 2.
 * Yii provides a flexible logging service to record and process messages.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-log-logger
 */

namespace yii\log;

use Yii;

/**
 * Logger records log messages in memory.
 * 
 * When the list of messages reaches a certain limit, it exports them to log targets.
 */
class Logger
{
    /**
     * Log level constants
     */
    const LEVEL_ERROR = 0x01;
    const LEVEL_WARNING = 0x02;
    const LEVEL_INFO = 0x04;
    const LEVEL_TRACE = 0x08;
    const LEVEL_PROFILE = 0x40;
    const LEVEL_PROFILE_BEGIN = 0x50;
    const LEVEL_PROFILE_END = 0x60;

    /**
     * Logs a message with the given level and category.
     * 
     * Usage via Yii static helper:
     * ```php
     * Yii::info('This is an info message', 'category');
     * Yii::error('This is an error message', 'category');
     * Yii::warning('This is a warning message', 'category');
     * Yii::trace('This is a trace message', 'category');
     * ```
     * 
     * @param string|array $message the message to be logged.
     * @param int $level the level of the message.
     * @param string $category the category of the message.
     */
    public function log($message, $level, $category = 'application')
    {
    }
}

/**
 * Yii Helper for Logging
 */
class LogHelper
{
    public static function trace($message, $category = 'application')
    {
        Yii::trace($message, $category);
    }

    public static function error($message, $category = 'application')
    {
        Yii::error($message, $category);
    }

    public static function warning($message, $category = 'application')
    {
        Yii::warning($message, $category);
    }

    public static function info($message, $category = 'application')
    {
        Yii::info($message, $category);
    }
}
\n```
