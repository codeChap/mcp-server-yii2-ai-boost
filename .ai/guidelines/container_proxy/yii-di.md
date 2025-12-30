```php
<?php

/**
 * AI Guideline: Yii 2.0 Dependency Injection & Service Locator
 * 
 * This file serves as a reference for DI and Service Locator in Yii 2.
 * 
 * @see https://www.yiiframework.com/doc/api/2.0/yii-di-container
 * @see https://www.yiiframework.com/doc/api/2.0/yii-di-servicelocator
 */

namespace yii\di;

/**
 * Container implements a dependency injection container.
 */
class Container extends Component
{
    /**
     * Registers a class definition with this container.
     * 
     * @param string $class class name, interface name or alias name
     * @param mixed $definition the definition associated with $class
     * @param array $params the list of constructor parameters
     */
    public function set($class, $definition = [], array $params = [])
    {
    }

    /**
     * Registers a class definition with this container and marks the class as a singleton.
     * 
     * @param string $class class name, interface name or alias name
     * @param mixed $definition the definition associated with $class
     * @param array $params the list of constructor parameters
     */
    public function setSingleton($class, $definition = [], array $params = [])
    {
    }

    /**
     * Returns an instance of the requested class.
     * 
     * @param string $class the class name or an alias name
     * @param array $params the list of constructor parameters
     * @param array $config the configuration array to be used for creating the new instance
     * @return object the instance of the requested class
     */
    public function get($class, $params = [], $config = [])
    {
        return new $class;
    }
}

/**
 * ServiceLocator supplies the application with components.
 * 
 * Yii::$app is a ServiceLocator.
 */
class ServiceLocator extends Component
{
    /**
     * @var array list of component definitions or instances
     */
    public function getComponents()
    {
        return [];
    }

    /**
     * Registers a set of component definitions.
     */
    public function setComponents($components)
    {
    }

    /**
     * Returns the component instance with the specified ID.
     * 
     * @param string $id component ID (e.g. 'db').
     * @return object|null the component of the specified ID, null if not found.
     */
    public function get($id, $throwException = true)
    {
        return null;
    }
}
\n```
