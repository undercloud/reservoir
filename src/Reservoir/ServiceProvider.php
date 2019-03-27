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
     * Register service provider
     *
     * @param mixed $container instance
     *
     * @return null
     */
    abstract public function register($container);
}
