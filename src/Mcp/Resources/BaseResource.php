<?php

namespace codechap\yii2boost\Mcp\Resources;

use yii\base\Component;

/**
 * Base class for MCP Resources
 *
 * Resources in MCP provide static content that can be read by clients.
 */
abstract class BaseResource extends Component
{
    /**
     * @var string Base path to the Yii2 application
     */
    public $basePath;

    /**
     * Get the resource name
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Get the resource description
     *
     * @return string
     */
    abstract public function getDescription();

    /**
     * Read the resource content
     *
     * @return mixed Resource content
     */
    abstract public function read();
}
