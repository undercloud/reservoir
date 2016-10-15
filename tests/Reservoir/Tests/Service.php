<?php
class Service extends Reservoir\ServiceProvider
{
    public function register($di)
    {
        $di->bind('xfoo', function($di){
            return $di->make('Foo');
        });
    }
}
?>