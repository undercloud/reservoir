# Reservoir
[![Build Status](https://travis-ci.org/undercloud/reservoir.svg?branch=master)](https://travis-ci.org/undercloud/reservoir)

##Installation
Install by composer
`composer require undercloud/reservoir`

##Usage

Create container instance
```PHP
$di = new Reservoir\Di;
```

###instance

```PHP
$di->instance('foo', new Bar);
```

Regiser callback that 
```PHP
$di->instance('foo', function ($di) {
    ...
});
```

###singleton

```PHP
$di->singleton('db', function(Reservoir\Di $di) {
    return new DataBase(
        $di->make('settings')->name,
        $di->make('settings')->user,
        $di->make('settings')->pass
    );
});
```

###bind

```PHP
$di->bind('database', function(Reservoir\Di $di) {
    return new Factory(
        $di->make('foo'),
        $di->make('bar')
    );
});
```

```PHP
$di->bind('App\Database\Abstract', 'App\Database\Mysql');
```

###alias

```PHP
$di->alias('db', 'database');
```

###isAlias

```PHP
// true
$di->isAlias('db');
```

###decorator

```PHP
$di->decorator('db', function($db, Reservoir\Di $di) {
    $decorator = new Decorator($db);

    return $decorator;
});
```

###fork

```PHP
$di->fork('db', function($db, Reservoir\Di $di) {
    $mongo = (clone $db)->setDriver('mongo');

    $di->instance('mongo', $mongo);
})
```

##Resolve

###make

```PHP
$di->make('foo');
```

```PHP
$di->make
```

###makes

```PHP
// [Foo, Bar]
list($foo, $bar) = $di->makes('foo', 'bar');
```

##Shortcut

```PHP
$bar = $di['foo'];
```

```PHP
// equivalent to $di->bind('foo', $bar)
$di['foo'] = $bar;
```

```PHP
isset($di['foo']);
```

```PHP
unset($di['foo']);
```

##Context

###when
###context
###isOverriden
###getOverride

##ServiceProvider


```PHP
use Reservoir\ServiceProvider

class DatabaseServiceProvider extends ServiceProvider
{
    public function register($di)
    {
        $di->singleton('db', function($di) {
            return new DatabaseConnection(
                $di->make('host'),
                $di->make('user'),
                $di->make('pass')
            );
        });
    }
}
```

Register service provider
```PHP
$di->register(new DatabaseServiceProvider);
```

###deferred providers


```PHP
class DatabaseServiceProvider extends ServiceProvider
{
    public $deferred = true;

    public $provides = ['db'];

    public function register($di)
    {
        ...
    }
}
```

##Utils

###has

Check if key registered
```PHP
// true
$di->has('foo')
```

###keys

Get all registered keys
```PHP
// ['foo','bar',...]
$di->keys()
```

###forget

Remove instance
```PHP
$di->forget('foo')
```

###flush

Clear all
```PHP
$di->flush()
```
