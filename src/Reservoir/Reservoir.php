<?php
require_once __DIR__ . '/Container.php';
require_once __DIR__ . '/Di.php';
require_once __DIR__ . '/ServiceProvider.php';
require_once __DIR__ . '/ContainerException.php';
require_once __DIR__ . '/Reflector.php';
require_once __DIR__ . '/ContextBinder.php';
require_once __DIR__ . '/PersistentStorage.php';
require_once __DIR__ . '/HashMap.php';

/*exit();

$class = new ReflectionClass($di);

var_dump(
    array_map(function($item)use($class){
        return (string)$item->getName();
    },array_filter($class->getMethods(),function($m){
        return $m->isPublic();
    }))
);
?>*/