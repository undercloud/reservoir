# Reservoir
[![Build Status](https://travis-ci.org/undercloud/reservoir.svg?branch=master)](https://travis-ci.org/undercloud/reservoir)

Inspired by [Laravel's Service Container](https://laravel.com/docs/master/container)

> In software engineering, dependency injection is a technique whereby one object (or static method) supplies the dependencies of another object. A dependency is an object that can be used (a service).

## Installation

`composer require undercloud/reservoir`

## Usage

Create container instance:
```php
$di = new Reservoir\Di;
```

### Instance

You may bind an existing object instance into the container using the `instance` method. The given instance will always be returned on subsequent calls into the container:
```php
$di->instance('foo', new Bar);
```

### Singleton

The `singleton` method binds a class or interface into the container that should only be resolved one time. Once a singleton binding is resolved, the same object instance will be returned on subsequent calls into the container:

```PHP
$di->singleton('database', function(Reservoir\Di $di) {
    return new DataBase(
        $di->make('settings')->driver,
        $di->make('settings')->user,
        $di->make('settings')->pass
    );
});
```

### Bind

We can register a binding using the `bind` method, passing the class or interface name that we wish to register along with a `Closure` that returns an instance of the class:

```PHP
$di->bind('autoloader', function(Reservoir\Di $di) {
    return new Autoloader(
        $di->make('include-path')
    );
});
```

A very powerful feature of the service container is its ability to bind an interface to a given implementation:

```PHP

namespace App\Database;

class Driver
{
    public function __construct(Abstract $driver)
    {
        ...
    }
}

$di->bind('App\Database\Abstract', 'App\Database\Mysql');

// App\Database\Mysql
$di->make('App\Database\Driver')
```

### Alias

To support both a class/interface and a short name simultaneously, use `alias`:

```PHP
$di->alias('db', 'App\Database\Mysql');
```

### Decorator

If you want to add additional functionality to an existing binding in the container, use the `decorator` method:

```PHP
$di->decorator('database', function($db, Reservoir\Di $di) {
    $decorator = new DatabaseDecorator($db);

    return $decorator;
});
```

### Fork

Sometimes it is required to create a copy of an existing entity in a container, it is possible to do this via the `fork` method, the existing binding will be retrieved from the container and cloned:

```PHP
$di->fork('db', function($db, Reservoir\Di $di) {
    $mongo = $db->setDriver('mongo');

    $di->instance('mongo', $mongo);
})
```

## Resolve

### make

Simple entity extraction:

```PHP
$di->make('foo');
```

Resolve class:

```PHP
$di->make('App\Database\Mysql');
```

Resolve method:
```PHP
$di->make('Foo::bar');

$di->make(['Foo', 'bar']);

$di->make([$foo, 'bar']);
```

Magic `__invoke`

```PHP

class Foo 
{
    public function __invoke(Bar $bar)
    {
        /* ... */
    }
}

$foo = new Foo;

$di->make($foo);
```

Extract entity by `Closure`:

```PHP
$di->make(function(Foo $foo, Bar $bar){
    /* ... */
})
```

### makes

Retrieving entity list:

```PHP
// [Foo, Bar]
list($foo, $bar) = $di->makes('foo', 'bar');

// [Foo, Bar]
list($foo, $bar) = $di->makes(['foo', 'bar']);
```

## Binding Primitives

Sometimes you may have a class that receives some injected classes, but also needs an injected primitive value such as an integer. You may easily use contextual binding to inject any value your class may need:

```PHP
$di->when('DateTime')
    ->needs('$time')
    ->give($timestamp);
```

## Contextual Binding

Sometimes you may have two classes that utilize the same interface, but you wish to inject different implementations into each class:

```PHP
$di->when('DateTime')
    ->needs('DateTimeZone')
    ->give(function () {
        return new DateTimeZone("EDT");
    });
```

## ServiceProvider

All service providers extend the `Reservoir\ServiceProvider` class. Most service providers contain a `register` method:

```PHP
use Reservoir\Di;
use Reservoir\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function register(Di $di)
    {
        $di->singleton('db', function(Di $di) {
            return new DatabaseConnection(
                $di->make('host'),
                $di->make('user'),
                $di->make('pass')
            );
        });
    }
}
```

Register service provider:

```PHP
$di->register('DatabaseServiceProvider');
```

### Deferred providers

If your provider is only registering bindings in the service container, you may choose to defer its registration until one of the registered bindings is actually needed. Deferring the loading of such a provider will improve the performance of your application, since it is not loaded from the filesystem on every request:

```PHP
use Reservoir\Di;
use Reservoir\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public $deferred = true;

    public function provides()
    {
        return ['db'];
    }

    public function register(Di $di)
    {
        ...
    }
}
```

Register deferred service provider:

```PHP
$di->register('DatabaseServiceProvider');
```

## Utils

### has

Check if key registered:

```PHP
// true
$di->has('foo')
```

### keys

Get all registered keys:

```PHP
// ['foo','bar',...]
$di->keys()
```

### forget

Remove instance:

```PHP
$di->forget('foo')
```

### flush

Clear all registered keys:

```PHP
$di->flush()
```
