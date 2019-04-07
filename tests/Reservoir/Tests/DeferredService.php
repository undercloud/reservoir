<?php
class DeferredService extends Reservoir\ServiceProvider
{
    public $deferred = true;

    public function provides()
    {
        return ['xbaz'];
    }

    public function register()
    {
        $this->di->bind('xbaz', function($di){
            return $di->make('Baz');
        });
    }
}