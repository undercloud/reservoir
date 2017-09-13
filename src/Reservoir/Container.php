<?php
namespace Reservoir;

use Closure;
use ReflectionClass;
use ReflectionException;

/**
 * Container API
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
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

    /**
     * Helper for resolve container value
     *
     * @param mixed   $entity     value
     * @param array   $additional params
     * @param boolean $raw        is raw value
     *
     * @return mixed
     */
    public function resolve($entity, array $additional = [], $raw = false)
    {
        if ($entity instanceof Closure) {
            return call_user_func($entity, $this);
        }

        return $raw ? $entity : $this->make($entity, $additional);
    }

    /**
     * Prevent adding duplicate keys
     *
     * @param string $key key
     *
     * @throws Reservoir\ContainerEception
     *
     * @return boolean
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

    /**
     * Check key exists
     *
     * @param string $key container key
     *
     * @return boolean
     */
    public function has($key)
    {
        return $this->persistentStorage->has($key);
    }

    /**
     * Register instance
     *
     * @param string $key      name
     * @param mixed  $resolver instance
     *
     * @throws ContainerException
     *
     * @return self
     */
    public function instance($key, $resolver)
    {
        $this->check($key);
        $this->persistentStorage->instances[$key] = $resolver;

        return $this;
    }

    /**
     * Register singleton
     *
     * @param string         $key      name
     * @param string|Closure $resolver callback
     *
     * @throws ContainerException
     *
     * @return self
     */
    public function singleton($key, $resolver)
    {
        if (!is_string($resolver) and !($resolver instanceof Closure)) {
            throw new ContainerException(
                sprintf(
                    'Argument 2 must be string or Closure, %s given',
                    gettype($resolver)
                )
            );
        }

        $this->check($key);
        $this->persistentStorage->singletones[$key] = $resolver;

        return $this;
    }

    /**
     * Register factory
     *
     * @param string         $key      name
     * @param string|Closure $resolver callback
     *
     * @return self
     */
    public function bind($key, $resolver)
    {
        if (!is_string($resolver) and !($resolver instanceof Closure)) {
            throw new ContainerException(
                sprintf(
                    'Argument 2 must be string or Closure, %s given',
                    gettype($resolver)
                )
            );
        }

        $this->check($key);
        $this->persistentStorage->registry[$key] = $resolver;

        return $this;
    }

    /**
     * Create alias for container key
     *
     * @param string $alias    name
     * @param string $abstract key
     *
     * @return self
     */
    public function alias($alias, $abstract)
    {
        $this->check($alias);
        $this->persistentStorage->aliases[$alias] = $abstract;

        return $this;
    }

    /**
     * Check if alias exists
     *
     * @param string $key alias
     *
     * @return boolean
     */
    public function isAlias($key)
    {
        return $this->persistentStorage->aliases->has($key);
    }

    /**
     * Extends existed container value
     *
     * @param string $key   name
     * @param mixed  $value new value
     *
     * @throws Reservoir\ContainerException
     *
     * @return self
     */
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

        return $this;
    }

    /**
     * Fork container value
     *
     * @param string  $key  name
     * @param Closure $call resolver
     *
     * @return self
     */
    public function fork($key, Closure $call)
    {
        if (!$this->has($key)) {
            throw new ContainerException(
                sprintf('Target %s does not exists', $key)
            );
        }

        $reference = $this->persistentStorage->getSourceReference($key)[$key];
        if (is_array($reference)) {
            $copy = [];
            foreach ($reference as $key => $value) {
                $copy[$key] = is_object($value) ? clone $value : $value;
            }

            call_user_func_array($call, [$copy, $this]);
        } elseif (is_object($reference)) {
            call_user_func_array($call, [clone $reference, $this]);
        } else {
            call_user_func_array($call, [$reference, $this]);
        }

        return $this;
    }

    /**
     * Remove container value by key
     *
     * @param string $key for delete
     *
     * @return bool
     */
    public function forget($key)
    {
        return $this->persistentStorage->forget($key);
    }

    /**
     * Clear container
     *
     * @return null
     */
    public function flush()
    {
        return $this->persistentStorage->flush();
    }

    /**
     * Register deferred services
     *
     * @param string $key name
     *
     * @return null
     */
    private function resolveDeferred($key)
    {
        foreach ($this->persistentStorage->deferred as $class => $map) {
            if (in_array($key, $map['provides'], true)) {
                $this->invokeRegister($map['instance']);
                $this->persistentStorage->deferred->del($class);
            }
        }
    }

    /**
     * Register service
     *
     * @param ServiceProvide $instance value
     *
     * @return null
     */
    private function invokeRegister(ServiceProvider $instance)
    {
        call_user_func([$instance, 'register'], $this);
    }

    /**
     * Register service logic
     *
     * @param ServiceProvide $instance value
     *
     * @return null
     */
    public function register(ServiceProvider $instance)
    {
        if (true === $instance->deferred) {
            $provides = $instance->provides;

            if ($provides) {
                $provides = (array) $provides;

                $key = get_class($instance);
                $this->persistentStorage->deferred[$key] = [
                    'instance' => $instance,
                    'provides' => $provides
                ];
            }
        } else {
            $this->invokeRegister($instance);
        }
    }

    /**
     * Return list of requested services
     *
     * @param mixed $keys,... services list
     *
     * @return array
     */
    public function makes()
    {
        $thisis = $this;
        $args = func_get_args();
        $callback = function ($key) use ($thisis) {
            return $thisis->make($key);
        };

        return array_map($callback, $args);
    }

    /**
     * Retrieve service by key
     *
     * @param string $key        name
     * @param array  $additional parameters
     *
     * @return mixed
     */
    public function make($key, array $additional = [])
    {
        if (is_array($key) or $key instanceof Closure) {
            return $this->reflector->reflect($key, $additional);
        }

        $storage = $this->persistentStorage;

        if ($this->isAlias($key)) {
            $key = $storage->aliases[$key];
        }

        $this->resolveDeferred($key);

        if ($storage->instances->has($key)) {
            return $storage->instances[$key];
        }

        if ($storage->registry->has($key)) {
            $registry = $storage->registry[$key];
            $instance = $this->resolve($registry, $additional);

            return $instance;
        } elseif ($storage->singletones->has($key)) {
            $singleton = $storage->singletones[$key];
            $instance = $this->resolve($singleton, $additional);
            $storage->instances[$key] = $instance;
            $storage->singletones->del($key);

            return $instance;
        } else {
            return $this->reflector->reflect($key, $additional);
        }
    }
}
