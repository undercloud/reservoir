<?php
namespace Reservoir;

use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use ReflectionException;

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
     * @param Container $container instance
     */
    public function __construct(Container $container)
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
        $parameterClass = $parameter->getClass();
        if ($parameterClass) {
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
                } elseif ($parameter->getClass()) {
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
            if (is_array($key)) {
                list($instance, $method) = $key;

                $reflection = new ReflectionMethod($instance, $method);
                $parameters = $reflection->getParameters();

                $arguments = $this->buildArguments(
                    get_class($instance),
                    $parameters,
                    $additional
                );

                if (!is_object($instance)) {
                    $instance = $this->container->make($instance);
                }

                return $reflection->invokeArgs($instance, $arguments);
            }

            if ($key instanceof Closure) {
                $reflection = new ReflectionFunction($key);

                $parameters = $reflection->getParameters();
                $arguments = $this->buildArguments(
                    'Closure',
                    $parameters,
                    $additional
                );

                return $reflection->invokeArgs($arguments);
            } else {
                if (false !== strpos($key, '::')) {
                    list($class, $method) = explode('::', $key);
                } else {
                    $class = $key;
                }

                $reflection = new ReflectionClass($class);
                if (false == $reflection->isInstantiable()) {
                    throw new ContainerException(
                        sprintf('%s is not instantiable', $class)
                    );
                }

                $constructor = $reflection->getConstructor();
                if ($constructor) {
                    $parameters = $constructor->getParameters();
                    $arguments = $this->buildArguments(
                        $class,
                        $parameters,
                        $additional
                    );
                    $instance = $reflection->newInstanceArgs($arguments);
                } else {
                    $instance = $reflection->newInstance();
                }

                if (isset($method)) {
                    $method = $reflection->getMethod($method);
                    $parameters = $method->getParameters();
                    $methodArguments = $this->buildArguments(
                        $class,
                        $parameters,
                        $additional
                    );

                    return $method->invokeArgs($instance, $methodArguments);
                } else {
                    return $instance;
                }
            }
        } catch (ReflectionException $e) {
            throw new ContainerException(
                $e->getMessage(),
                $e->getCode()
            );
        }
    }
}
