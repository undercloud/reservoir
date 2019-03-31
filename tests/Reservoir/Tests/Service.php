<?php
class Service extends Reservoir\ServiceProvider
{
    public function register(Reservoir\Di $di)
    {
        $di->bind('xfoo', function($di){
            return $di->make('Foo');
        });
    }
}