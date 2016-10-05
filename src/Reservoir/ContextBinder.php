<?php
namespace Reservoir;

/**
 * Context binder
 */
class ContextBinder
{
    /**
     * @var Reservoir\Container instance
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
     * @return Reservoir\ContextBinder
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
     * @return Reservoir\Di
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
?>