```php
<?php

/**
 * AI Guideline: Yii 2.0 Framework Specific Utilities
 * 
 * This file serves as a reference for Yii-specific utility classes.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-helpers-arrayhelper
 * @see https://www.yiiframework.com/doc/api/2.0/yii-helpers-url
 */

namespace yii\helpers;

/**
 * ArrayHelper provides additional array functionality.
 */
class ArrayHelper
{
    /**
     * Retrieves the value of an array element or object property with the given key or property name.
     */
    public static function getValue($array, $key, $default = null) {}

    /**
     * Index an array according to a specified key.
     */
    public static function index($array, $key) {}

    /**
     * Builds a map (key-value pairs) from a multidimensional array or an array of objects.
     */
    public static function map($array, $from, $to, $group = null) {}
}

/**
 * Url provides static methods to manage URLs.
 */
class Url
{
    /**
     * Creates a URL for the given route.
     * 
     * @param array|string $route
     * @param bool|string $scheme
     * @return string
     */
    public static function to($route, $scheme = false) {}

    /**
     * Creates a URL based on the given route.
     */
    public static function toRoute($route, $scheme = false) {}

    /**
     * Returns the current URL.
     */
    public static function current(array $params = [], $scheme = false) {}
}
\n```
