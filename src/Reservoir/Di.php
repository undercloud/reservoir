<?php
namespace Reservoir;

use Closure;
use ArrayAccess;

/**
 * Dependency Injection API
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class Di extends Container implements ArrayAccess
{
    /**
     * Initialize instance
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * ArrayAccess::offsetExists
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * ArrayAccess::offsetGet
     *
     * @param string $key key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * ArrayAccess::offsetSet
     *
     * @param string $key   key
     * @param mixed  $value value
     *
     * @return null
     */
    public function offsetSet($key, $value)
    {
        if (null === $key) {
            throw new ContainerException('Cannot register empty key');
        }

        if (!($value instanceof Closure)) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $this->bind($key, $value);
    }

    /**
     * ArrayAccess::offsetUnset
     *
     * @param string $key key
     *
     * @return null
     */
    public function offsetUnset($key)
    {
        $this->forget($key);
    }

    /**
     * Initialize context binder
     *
     * @param mixed $concrete value
     *
     * @return Reservoir\ContextBinder
     */
    public function when($concrete)
    {
        return new ContextBinder($this, $concrete);
    }

    /**
     * Build context
     *
     * @param string $concrete       value
     * @param string $needs          value
     * @param mixed  $implementation value
     *
     * @return Reservoir\Di
     */
    public function context($concrete, $needs, $implementation)
    {
        $context = $this->persistentStorage->context;
        if (!$context->has($concrete)) {
            $context[$concrete] = new HashMap;
        }

        $context[$concrete][$needs] = $implementation;

        return $this;
    }

    /**
     * Check override context
     *
     * @param mixed $concrete value
     * @param mixed $needs    value
     *
     * @return boolean
     */
    public function isOverriden($concrete, $needs)
    {
        return (
            $this->persistentStorage->context->has($concrete)
            and isset($this->persistentStorage->context[$concrete][$needs])
        );
    }

    /**
     * Get override context
     *
     * @param mixed $concrete value
     * @param mixed $needs    value
     *
     * @return mixed
     */
    public function getOverride($concrete, $needs)
    {
        return $this->persistentStorage->context[$concrete][$needs];
    }
}
?>