<?php
namespace Reservoir;

/**
 * Service provider base class
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
abstract class ServiceProvider
{
    /**
     * @var bool
     */
    public $deferred = false;

    /**
     * Get provided keys
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

    /*
     * @param Di $container instance
     */
    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    /**
     * Register service provider
     *
     * @return null
     */
    abstract public function register();
}
