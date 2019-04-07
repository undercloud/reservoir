<?php
class Service extends Reservoir\ServiceProvider
{
    public function register()
    {
        $this->di->bind('xfoo', function($di){
            return $di->make('Foo');
        });
    }
}