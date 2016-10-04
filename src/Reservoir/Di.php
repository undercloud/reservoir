<?php
namespace Reservoir;

use Closure;
use ArrayAccess;

/**
 * Dependency Injection API
 */
class Di extends Container implements ArrayAccess
{
    /**
     * Initialize instance
     */
    public function __construct($scope = null)
    {
        parent::__construct();
    }

    /**
     * ArrayAccess::offsetExists
     *
     * @param string $key key
     *
     * @return bool
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

    private function resolveDeferred($key)
    {
        foreach ($this->persistentStorage->deferred as $class => $map) {
            if (in_array($key, $map['provides'], true)) {
                $this->invokeRegister($map['instance']);
                $this->persistentStorage->deferred->del($class);
            }
        }
    }

    private function invokeRegister($instance)
    {
        call_user_func([$instance,'register'], $this);
    }

    public function register($instance)
    {
        if (true === $instance->deferred) {
            $provides = $instance->provides;

            if ($provides) {
                if (!is_array($provides)) {
                    $provides = [$provides];
                }

                $key = get_class($provider);
                $this->persistentStorage->deferred[$key] = [
                    'instance' => $instance,
                    'provides' => $provides
                ];
            }
        } else {
            $this->invokeRegister($instance);
        }
    }
}
?>