<?php
namespace Reservoir\Di;

use Closure;
use ArrayAccess;

class Di extends Container implements ArrayAccess
{
    public function __construct()
    {
        parent::__construct();
    }

    public function offsetExists($key)
    {
        return $this->has($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

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

    public function offsetUnset($key)
    {
        $this->forget($key);
    }

    public function when($concrete)
    {
        return new ContextBinder($this, $concrete);
    }

    public function context($concrete, $needs, $implementation)
    {
        $context = $this->persistentStorage->context;
        if(!$context->has($concrete)){
            $context[$concrete] = new HashMap;
        }

        $context[$concrete][$needs] = $implementation;

        return $this;
    }

    public function isOverriden($concrete, $needs)
    {
        return (
            $this->persistentStorage->context->has($concrete)
            and isset($this->persistentStorage->context[$concrete][$needs])
        );
    }

    public function getOverride($concrete, $needs)
    {
        return $this->persistentStorage->context[$concrete][$needs];
    }
}
?>