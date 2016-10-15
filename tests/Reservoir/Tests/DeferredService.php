<?php
class DeferredService extends Reservoir\ServiceProvider
{
    public $deferred = true;
    public $provides = 'xbaz';

    public function register($di)
    {
        $di->bind('xbaz', function($di){
            return $di->make('Baz');
        });
    }
}
?>