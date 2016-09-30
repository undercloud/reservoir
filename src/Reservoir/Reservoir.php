<?php
require_once __DIR__ . '/Container.php';
require_once __DIR__ . '/Di.php';
require_once __DIR__ . '/ContainerException.php';
require_once __DIR__ . '/Reflector.php';
require_once __DIR__ . '/ContextBinder.php';
require_once __DIR__ . '/PersistentStorage.php';
require_once __DIR__ . '/HashMap.php';


class Mongo {
    private $hash;
    public function __construct(){
        $this->hash = uniqid();;
    }
}

$di = new Reservoir\Di;

$di->singleton('db', function(){
    return new Mongo;
});

var_dump(
$di->make('db'),
$di->make('db'),
$di->make('db'),
$di->make('db'),
$di->make('db')
);
exit();

$class = new ReflectionClass($di);

var_dump(
    array_map(function($item)use($class){
        return (string)$item->getName();
    },array_filter($class->getMethods(),function($m){
        return $m->isPublic();
    }))
);
?>