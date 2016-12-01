# Reservoir
[![Build Status](https://travis-ci.org/undercloud/reservoir.svg?branch=master)](https://travis-ci.org/undercloud/reservoir)
##Todo
fork
##Installation

##usage

###instance
```PHP
$di->instance('foo', new Bar);
```

###singleton
```PHP
$di->singleton('db', function($di) {
    return new DataBase(
        $di->make('settings')->name,
        $di->make('settings')->user,
        $di->make('settings')->pass
    );
});
```

###bind
```PHP
$di->singleton('database', function($di) {
    return new Factory(
        $di->make('foo'),
        $di->make('bar')
    );
});
```

###alias
```PHP
$di->alias('db', 'database');
```

###isAlias
```PHP
// true
$di->isAlias('db')
```

###decorator
```PHP
$di->decorator('db', function($db, $di) {
    $decorator = new Decorator($db);

    return $decorator;
});
```

###make
```PHP
$di->make('foo');
```
###makes
```PHP
// [Foo, Bar]
list($foo, $bar) = $di->makes('foo', 'bar');
```

###when
###context
###isOverriden
###getOverride

##Utils
###has
```PHP
// true
$di->has('foo')
```
###keys
```PHP
// ['foo','bar',...]
$di->keys()
```
###forget
```PHP
$di->forget('foo')
```
###flush
```PHP
$di->flush()
```
