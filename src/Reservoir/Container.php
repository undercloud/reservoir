<?php
namespace Reservoir;

use Closure;

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
     * @var Reflector
     */
    protected $reflector;

    /**
     * @var PersistentStorage
     */
    protected $persistentStorage;

    /**
     * @var Pipe
     */
    protected $pipe;

    /**
     * Initialize instance
     */
    public function __construct()
    {
        $this->reflector = new Reflector($this);
        $this->persistentStorage = new PersistentStorage;
        $this->pipe = new Pipe;
    }

    /**
     * Helper for resolve container value
     *
     * @param mixed   $entity     value
     * @param array   $additional params
     * @param boolean $raw        is raw value
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    private function resolve($entity, array $additional = [], $raw = false)
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
     * @throws ContainerException
     *
     * @return void
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
        $this->persistentStorage->singletons[$key] = $resolver;

        return $this;
    }

    /**
     * Register factory
     *
     * @param string         $key      name
     * @param string|Closure $resolver callback
     *
     * @throws ContainerException
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
     * @throws ContainerException
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
     * @throws ContainerException
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
     * @throws ContainerException
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
     * @return void
     */
    public function flush()
    {
        $this->persistentStorage->flush();
    }

    /**
     * Register deferred services
     *
     * @param string $key name
     *
     * @throws ContainerException
     *
     * @return void
     */
    private function resolveDeferred($key)
    {
        foreach ($this->persistentStorage->deferred as $class => $provides) {
            if (in_array($key, $provides, true)) {
                $this->persistentStorage->deferred->del($class);

                if (is_string($class)) {
                    $class = $this->make($class);
                }

                $this->invokeRegister($class);
            }
        }
    }

    /**
     * Register service
     *
     * @param ServiceProvider $instance value
     *
     * @return void
     */
    private function invokeRegister(ServiceProvider $instance)
    {
        call_user_func([$instance, 'register'], $this);
    }

    /**
     * Register service logic
     *
     * @param ServiceProvider $instance value
     *
     * @return void
     */
    public function register(ServiceProvider $instance)
    {
        $this->invokeRegister($instance);
    }

    /**
     * Create deferred service provider
     *
     * @param mixed $provider
     * @param mixed $provides
     *
     * @return void
     */
    public function defer($provider, $provides)
    {
        $this->persistentStorage->deferred[$provider] = (array) $provides;
    }

    /**
     * Add container listener
     *
     * @param  Closure|string $target   name
     * @param  Closure|null   $callback handle
     *
     * @throws ContainerException
     *
     * @return void
     */
    public function resolving($target, Closure $callback = null)
    {
        if (func_num_args() === 1) {
            if (!($target instanceof Closure)) {
                throw new ContainerException(
                    sprintf(
                        'Argument 1 must be Closure, %s given',
                        gettype($target)
                    )
                );
            }

            $this->pipe->all($target);
        } else {
            $this->pipe->on($target, $callback);
        }
    }

    /**
     * Pipe preprocessor
     *
     * @param string $key name
     * @param mixed  $val item
     *
     * @return mixed
     */
    private function pipe($key, $val)
    {
        $this->pipe->fire($key, $val, $this);

        return $val;
    }

    /**
     * Return list of requested services
     *
     * @return array
     */
    public function makes()
    {
        $self = $this;
        $args = func_get_args();
        $callback = function ($key) use ($self) {
            return $self->make($key);
        };

        return array_map($callback, $args);
    }

    /**
     * Retrieve service by key
     *
     * @param mixed $key        name
     * @param array $additional parameters
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    public function make($key, array $additional = [])
    {
        if (is_array($key) or $key instanceof Closure) {
            $val = $this->reflector->reflect($key, $additional);

            return $this->pipe($key, $val);
        }

        $storage = $this->persistentStorage;

        if ($this->isAlias($key)) {
            $key = $storage->aliases[$key];
        }

        $this->resolveDeferred($key);

        if ($storage->instances->has($key)) {
            return $this->pipe($key, $storage->instances[$key]);
        }

        if ($storage->registry->has($key)) {
            $registry = $storage->registry[$key];
            $instance = $this->resolve($registry, $additional);

            return $this->pipe($key, $instance);
        }

        if ($storage->singletons->has($key)) {
            $singleton = $storage->singletons[$key];
            $instance = $this->resolve($singleton, $additional);
            $storage->instances[$key] = $instance;
            $storage->singletons->del($key);

            return $this->pipe($key, $instance);
        }

        $val = $this->reflector->reflect($key, $additional);

        return $this->pipe($key, $val);
    }
}
