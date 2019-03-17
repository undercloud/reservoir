<?php
namespace Reservoir;

/**
 * Context binder
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class ContextBinder
{
    /**
     * @var Di instance
     */
    protected $container;

    /**
     * @var mixed
     */
    protected $concrete;

    /**
     * @var string
     */
    protected $needs;

    /**
     * Initialize instance
     *
     * @param Container $container instance
     * @param mixed     $concrete  value
     */
    public function __construct(Container $container, $concrete)
    {
        $this->container = $container;
        $this->concrete = $concrete;
    }

    /**
     * Bind needs
     *
     * @param mixed $abstract value
     *
     * @return ContextBinder
     */
    public function needs($abstract)
    {
        $this->needs = $abstract;

        return $this;
    }

    /**
     * Bind implements
     *
     * @param mixed $implementation value
     *
     * @return Di
     */
    public function give($implementation)
    {
        return $this->container->context(
            $this->concrete,
            $this->needs,
            $implementation
        );
    }
}
