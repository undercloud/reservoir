<?php
namespace Reservoir;
/**
 * Service provider base class
 */
abstract class ServiceProvider
{
    /**
     * @var string|array list of deferred providers
     */
    public $provides;

    /**
     * @var boolean is service deferred
     */
    public $deferred = false;

    /**
     * Register service provider
     *
     * @param mixed $app application instance
     *
     * @return null
     */
    abstract public function register($app);
}
?>