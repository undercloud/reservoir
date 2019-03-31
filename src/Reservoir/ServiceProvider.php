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

    /**
     * Register service provider
     *
     * @param Di $container instance
     *
     * @return null
     */
    abstract public function register(Di $container);
}
