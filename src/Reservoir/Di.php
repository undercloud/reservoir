<?php
namespace Reservoir;

/**
 * Dependency Injection API
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class Di extends Container
{
    /**
     * Initialize context binder
     *
     * @param mixed $concrete value
     *
     * @return ContextBinder
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
     * @return Di
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
