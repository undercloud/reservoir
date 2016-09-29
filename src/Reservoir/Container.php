<?php
namespace Reservoir;

use Closure;
use ReflectionClass;
use ReflectionException;

class Container
{
    /**
     * @var Reservoir\Reflector
     */
    protected $reflector;

    /**
     * @var Reservoir\PersistentStorage
     */
    protected $persistentStorage;

    /**
     * Initialize instance
     */
    public function __construct()
    {
        $this->reflector = new Reflector($this);
        $this->persistentStorage = new PersistentStorage;
    }

    private function resolve($entity, array $additional = [], $raw = false)
    {
        if ($entity instanceof Closure) {
            return $this->reflector->reflect($entity, $additional);
        }

        if ($raw) {
            return $entity;
        }

        return $this->make($entity, $additional);
    }

    /**
     * Check key exists
     *
     * @param string $key key
     * @throws Reservoir\ContainerEception
     *
     * @return bool
     */
    private function check($key)
    {
        if ($this->has($key)) {
            throw new ContainerException(
                sprintf('Container already contains key: %s', $key)
            );
        }
    }

    /**
     * Return list of avail keys
     *
     * @return array
     */
    public function keys()
    {
        return $this->persistentStorage->keys();
    }

    public function has($key)
    {
        return $this->persistentStorage->has($key);
    }

    /**
     * [instance description]
     *
     * @param  string               $key      [description]
     * @param  mixed                $resolver [description]
     * @return Olifant\Di\Container           [description]
     */
    public function instance($key, $resolver)
    {
        $this->check($key);
        $this->persistentStorage->instances[$key] = $resolver;

        return $this;
    }

    public function singleton($key, $resolver)
    {
        $this->check($key);
        $this->persistentStorage->singletones[$key] = $resolver;

        return $this;
    }

    public function bind($key, $resolver)
    {
        $this->check($key);
        $this->persistentStorage->registry[$key] = $resolver;

        return $this;
    }

    public function alias($alias, $abstract)
    {
        $this->check($key);
        $this->persistentStorage->aliases[$alias] = $abstract;

        return $this;
    }

    public function isAlias($key)
    {
        return $this->persistentStorage->aliases->has($key);
    }

    public function decorator($key, $value)
    {
        if (!$this->has($key)) {
            throw new ContainerException(
                sprintf('Target %s does not exists', $key)
            );
        }

        if (!($value instanceof Closure)) {
            $value = function () use ($value) {
                return $value;
            };
        }

        $reference = $this->persistentStorage->getSourceReference($key);

        $reference[$key] = $value($reference[$key], $this);
    }

    public function forget($key)
    {
        return $this->persistentStorage->forget($key);
    }

    public function flush()
    {
        return $this->persistentStorage->flush();
    }

    public function makes()
    {
        $thisis = $this;
        $args = func_get_args();
        $callback = function ($key) use ($thisis) {
            return $thisis->make($key);
        };

        return array_map($callback, $args);
    }

    public function make($key, array $additional = [])
    {
        if (is_array($key) or $key instanceof Closure) {
            return $this->reflector->reflect($key, $additional);
        }

        $ps = $this->persistentStorage;

        if ($this-> isAlias($key)) {
            $key = $ps->aliases[$key];
        }

        if ($ps->instances->has($key)) {
            return $ps->instances[$key];
        }

        if ($ps->registry->has($key)) {
            $registry = $ps->registry[$key];
            $instance = $this->resolve($registry, $additional);

            return $instance;
        } else if ($ps->singletones->has($key)) {
            $singleton = $ps->singletones[$key];
            $instance = $this->resolve($singleton, $additional);
            $ps->instances[$key] = $instance;

            return $instance;
        } else {
            return $this->reflector->reflect($key, $additional);
        }
    }
}
?>