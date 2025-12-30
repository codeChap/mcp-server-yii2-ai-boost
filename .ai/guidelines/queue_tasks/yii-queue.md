```php
<?php

/**
 * AI Guideline: Yii 2.0 Queue (Extension) Structure
 * 
 * This file serves as a reference for using Queues in Yii 2 (yii2-queue).
 * Queues allow you to offload tasks to be processed asynchronously.
 * 
 * @see https://github.com/yiisoft/yii2-queue
 */

namespace yii\queue;

use yii\base\Component;
use yii\base\BaseObject;

/**
 * Job Interface.
 * 
 * All job classes must implement this interface.
 */
interface JobInterface
{
    /**
     * @param Queue $queue which pushed and is handling the job
     * @return void|mixed result of the job execution
     */
    public function execute($queue);
}

/**
 * Queue Component.
 * 
 * Handles pushing jobs to the queue.
 */
abstract class Queue extends Component
{
    /**
     * Pushes job into queue.
     * 
     * @param JobInterface|mixed $job
     * @return string|null id of the pushed message
     */
    public function push($job)
    {
        return 'job-id';
    }

    /**
     * Pushes job into queue with delay.
     * 
     * @param int $delay
     * @param JobInterface|mixed $job
     * @return string|null id of the pushed message
     */
    public function delay($delay, $job)
    {
        return 'job-id';
    }
}

/**
 * Example Job Class
 */
class ExampleJob extends BaseObject implements JobInterface
{
    public $url;
    public $file;
    
    public function execute($queue)
    {
        file_put_contents($this->file, file_get_contents($this->url));
    }
}
\n```
