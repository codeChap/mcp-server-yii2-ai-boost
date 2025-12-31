# Yii2 Console Controller

```php
namespace yii\console;

use yii\base\Controller as BaseController;

class Controller extends BaseController
{
    /** @var bool run interactively */
    public $interactive = true;
    
    /** @var bool|null enable ANSI colors */
    public $color;

    /**
     * Define available options for action
     * @return string[] option names (public properties)
     */
    public function options($actionID)
    {
        return ['color', 'interactive', 'help'];
    }

    /**
     * Define short aliases: ['c' => 'color']
     */
    public function optionAliases()
    {
        return [];
    }

    // Output methods
    public function stdout($string);           // Print to STDOUT
    public function stderr($string);           // Print to STDERR

    // Input methods
    public function prompt($text, $options = []);      // Get text input
    public function confirm($message, $default = false); // Yes/no confirmation
    public function select($message, $options = []);   // Choose from options

    // Exit codes
    const EXIT_CODE_NORMAL = 0;
    const EXIT_CODE_ERROR = 1;
}
```

## Usage Example
```php
namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

class ExampleController extends Controller
{
    public $verbose = false;

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['verbose']);
    }

    public function actionIndex($message = 'hello')
    {
        $this->stdout($message . "\n", 32); // Green
        return ExitCode::OK;
    }
}
```

Run: `php yii example "hello world" --verbose`
