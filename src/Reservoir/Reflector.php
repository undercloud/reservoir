<?php
namespace Reservoir;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionException;
use InvalidArgumentException;

/**
 * Reflection API
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class Reflector
{
    /**
     * @var Di instance
     */
    protected $container;

    /**
     * Initialize instance
     *
     * @param Di $container instance
     */
    public function __construct(Di $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve context
     *
     * @param ReflectionParameter $parameter instance
     *
     * @return mixed
     */
    private function buildContext(ReflectionParameter $parameter)
    {
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            $reflectionType = $parameter->getType();
            if ($reflectionType and $reflectionType->isBuiltin()) {
                $abstract = '$' . $parameter->getName();
            } else {
                $abstract = $parameter->getName();
            }
        } elseif (method_exists($parameter, 'getClass') and $parameterClass = $parameter->getClass()) {
            $abstract = $parameterClass->getName();
        } else {
            $abstract = '$' . $parameter->getName();
        }

        return $abstract;
    }

    /**
     * Check bind context
     *
     * @param mixed               $context   context
     * @param ReflectionParameter $parameter instance
     *
     * @return boolean
     */
    private function checkContext($context, ReflectionParameter $parameter)
    {
        $abstract = $this->buildContext($parameter);

        return $this->container->isOverriden($context, $abstract);
    }

    /**
     * Return bind context
     *
     * @param mixed               $context   context
     * @param ReflectionParameter $parameter instance
     *
     * @return mixed
     */
    private function getContext($context, ReflectionParameter $parameter)
    {
        $abstract = $this->buildContext($parameter);

        return $this->container->getOverride($context, $abstract);
    }

    /**
     * Build arguments list
     *
     * @param mixed $context    context
     * @param array $parameters list
     * @param array $additional parameters
     *
     * @throws ContainerException
     *
     * @return array
     */
    public function buildArguments($context, array $parameters, array $additional)
    {
        $callParams = [];
        foreach ($parameters as $parameter) {
            $parameterName = $parameter->getName();

            if (array_key_exists($parameterName, $additional)) {
                $callParams[] = $additional[$parameterName];
            } else {
                $hasContext = $this->checkContext($context, $parameter);

                if ($hasContext) {
                    $concreteContext = $this->getContext($context, $parameter);
                    $callParams[] = $this->container->resolve(
                        $concreteContext,
                        [],
                        true
                    );
                } elseif (version_compare(PHP_VERSION, '8.0.0') >= 0) {
                    $reflectionType = $parameter->getType();
                    if ($reflectionType and $reflectionType->isBuiltin()) {
                        if ($parameter->isDefaultValueAvailable()) {
                            $callParams[] = $parameter->getDefaultValue();
                        }
                    } else {
                        $classname = $parameter->getName();
                        $callParams[] = $this->container->make($classname);
                    }
                } elseif (method_exists($parameter, 'getClass') and $parameter->getClass()) {
                    $classname = $parameter->getClass()->getName();
                    $callParams[] = $this->container->make($classname);
                } else {
                    if ($parameter->isDefaultValueAvailable()) {
                        $callParams[] = $parameter->getDefaultValue();
                    }
                }
            }
        }

        return $callParams;
    }

    /**
     * Normalize callable entity
     *
     * @param mixed $key callable entity
     *
     * @return array
     */
    private function normalizeCallable($key)
    {
        $instance = $method = null;

        if ($key instanceof Closure) {
            $method = $key;
        } elseif (is_string($key) and false !== strpos($key, '::')) {
            list($instance, $method) = explode('::', $key);
        } elseif (is_string($key)) {
            $instance = $key;
        } elseif (is_object($key)) {
            $instance = $key;
            $method = '__invoke';
        } else if (is_array($key)) {
            list($instance, $method) = $key;
        }

        if (!isset($instance) and !isset($method)) {
            throw new InvalidArgumentException(
                'Invalid callable entity'
            );
        }

        return [$instance, $method];
    }

    /**
     * Normalize instance
     *
     * @param mixed $instance   entity
     * @param array $additional arguments
     *
     * @throws ContainerException
     * @throws ReflectionException
     *
     * @return mixed
     */
    private function normalizeInstance($instance, array $additional)
    {
        if (null === $instance) {
            return;
        }

        if (is_string($instance)) {
            $reflection = new ReflectionClass($instance);
            if (false == $reflection->isInstantiable()) {
                throw new ContainerException(
                    sprintf('%s is not instantiable', $instance)
                );
            }

            $constructor = $reflection->getConstructor();
            if ($constructor) {
                $parameters = $constructor->getParameters();
                $arguments = $this->buildArguments(
                    $instance,
                    $parameters,
                    $additional
                );
                $instance = $reflection->newInstanceArgs($arguments);
            } else {
                $instance = $reflection->newInstance();
            }
        }

        return $instance;
    }

    /**
     * Resolve given key
     *
     * @param string $key        key
     * @param array  $additional parameters
     *
     * @throws ContainerException
     *
     * @return mixed
     */
    public function reflect($key, array $additional = [])
    {
        try {
            list($instance, $method) = $this->normalizeCallable($key);

            $instance = $this->normalizeInstance($instance, $additional);

            if ($instance and $method) {
                $reflection = new ReflectionMethod($instance, $method);
                $class = get_class($instance);
            } elseif ($method) {
                $reflection = new ReflectionFunction($method);
                $class = 'Closure';
            } elseif ($instance) {
                return $instance;
            }

            $parameters = $reflection->getParameters();
            $arguments = $this->buildArguments(
                $class,
                $parameters,
                $additional
            );

            if ($reflection instanceof ReflectionFunction) {
                return $reflection->invokeArgs($arguments);
            } elseif ($reflection instanceof ReflectionMethod) {
                return $reflection->invokeArgs($instance, $arguments);
            }
        } catch (ReflectionException $e) {
            throw new ContainerException(
                $e->getMessage(),
                $e->getCode()
            );
        }
    }
}
