<?php
namespace Reservoir;

abstract class ServiceProvider
{
	public $provides;
	public $deferred = false;

	abstract public function register($app);
}
?>