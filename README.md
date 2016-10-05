# Reservoir

##Installation

##usage

###instance
```
$di->instance('foo', new Bar);
```

###singleton
```
$di->singleton('db', function($di){
    return new DataBase(
        $di->make('settings')->name,
        $di->make('settings')->user,
        $di->make('settings')->pass
    );
});
```

###bind
```
$di->singleton('database', function($di){
    return new Factory(
        $di->make('foo'),
        $di->make('bar')
    );
});
```

###alias
```
$di->alias('db', 'database');
```

###isAlias
```
// true
$di->isAlias('db')
```

###decorator
```
$di->decorator('db', function($db, $di){
    $decorator = new Decorator($db);

    return $decorator;
});
```

###makes
###make

###when
###context
###isOverriden
###getOverride

###has
###keys
###forget
###flush
