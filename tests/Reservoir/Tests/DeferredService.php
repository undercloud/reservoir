<?php
class DeferredService extends Reservoir\ServiceProvider
{
    public function register($di)
    {
        $di->bind('xbaz', function($di){
            return $di->make('Baz');
        });
    }
}