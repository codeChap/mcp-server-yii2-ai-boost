```php
<?php

/**
 * AI Guideline: Yii 2.0 Console Controller Structure
 * 
 * This file serves as a reference for creating Console Controllers in Yii 2.
 * It outlines the key properties, methods, and lifecycle events.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-console-controller
 */

namespace yii\console;

use yii\base\Controller as BaseController;

/**
 * Controller is the base class of console command classes.
 * 
 * A console controller consists of one or more actions known as sub-commands.
 * Users call a console command by specifying the route to the controller action: `yii <route>`.
 */
class Controller extends BaseController
{
    /**
     * @var bool whether to run the command interactively.
     */
    public $interactive = true;

    /**
     * @var bool|null whether to enable ANSI color in the output.
     * If not set, ANSI color will only be enabled for terminals that support it.
     */
    public $color;

    /**
     * @var string|null the help summary to display for this controller.
     * If null, it will be extracted from the docblock of the controller class.
     */
    public $helpSummary;

    /**
     * Returns the names of valid options for the action (id).
     * 
     * You should override this method to define the options available for a specific action.
     * The returned array should contain the names of public properties of the controller class.
     * 
     * Example:
     * ```php
     * public function options($actionID)
     * {
     *     return ['color', 'interactive'];
     * }
     * ```
     * 
     * @param string $actionID the action id of the current request
     * @return string[] the names of the options valid for the action
     */
    public function options($actionID)
    {
        return ['color', 'interactive', 'help'];
    }

    /**
     * Returns option aliases.
     * 
     * Override this to define short aliases for options.
     * 
     * Example:
     * ```php
     * public function optionAliases()
     * {
     *     return [
     *         'c' => 'color',
     *         'i' => 'interactive',
     *     ];
     * }
     * ```
     * 
     * @return array the aliases (alias => option)
     */
    public function optionAliases()
    {
        return [];
    }

    /**
     * This method is invoked right before an action is executed.
     * 
     * @param \yii\base\Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    /**
     * This method is invoked right after an action is executed.
     * 
     * @param \yii\base\Action $action the action just executed.
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }

    /**
     * Prints a string to STDOUT.
     * 
     * @param string $string the string to print
     * @return int|bool number of bytes printed or false on error
     */
    public function stdout($string)
    {
        // Implementation provided by Yii
        return fwrite(\STDOUT, $string);
    }

    /**
     * Prints a string to STDERR.
     * 
     * @param string $string the string to print
     * @return int|bool number of bytes printed or false on error
     */
    public function stderr($string)
    {
        // Implementation provided by Yii
        return fwrite(\STDERR, $string);
    }

    /**
     * Prompts the user for input and validates it.
     * 
     * @param string $text prompt string
     * @param array $options the options for validating the input:
     *  - required: whether it is required (true)
     *  - default: default value if no input is inserted
     *  - pattern: regular expression pattern to validate the input
     *  - validator: a callable function to validate input
     *  - error: the error message to display when validation fails
     * @return string the user input
     */
    public function prompt($text, $options = [])
    {
        // Implementation provided by Yii
        return '';
    }

    /**
     * Asks user to confirm by entering y or n.
     * 
     * @param string $message to print out before waiting for user input
     * @param bool $default this value is returned if no selection is made.
     * @return bool whether user confirmed
     */
    public function confirm($message, $default = false)
    {
        // Implementation provided by Yii
        return true;
    }

    /**
     * Gives the user an option to choose from.
     * 
     * @param string $message the message to display
     * @param array $options the options to choose from (key => value)
     * @param mixed $default the default value if no selection is made
     * @return mixed the selected value
     */
    public function select($message, $options = [], $default = null)
    {
        // Implementation provided by Yii
        return $default;
    }

    /**
     * Exit codes usually used in console commands.
     * Use these constants as return values for action methods.
     */
    const EXIT_CODE_NORMAL = 0;
    const EXIT_CODE_ERROR = 1;

    /**
     * Example Action
     * 
     * Arguments for this action are passed as method parameters.
     * 
     * @param string $message The message to print
     * @return int Exit code
     */
    public function actionIndex($message = 'hello')
    {
        $this->stdout($message . "\n");
        return self::EXIT_CODE_NORMAL;
    }
}
\n```
