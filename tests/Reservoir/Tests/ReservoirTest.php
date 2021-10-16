<?php
namespace Reservoir\Tests;

use Exception;
use Reservoir\Di;
use Reservoir\ContainerException;

class ReservoirTest extends ReservoirSetup
{
    public function testInstance()
    {
        $this->di->instance('foo', 'bar');

        $this->assertEquals(true, $this->di->has('foo'));

        $this->assertEquals(false, $this->di->has('bar'));
        $this->assertEquals(['foo'],$this->di->keys());

        $this->di->instance('bar', null);
        $this->di->instance('baz', null);
        $this->assertEquals(true, $this->di->forget('bar'));
        $this->assertEquals(['foo','baz'],$this->di->keys());

        $this->assertEquals($this->di->make('foo'),'bar');

        $this->di->flush();

        $this->assertEquals([], $this->di->keys());
    }

    public function testBind()
    {
        $this->di->bind('foo', function() {
            return 'bar';
        });

        $this->assertEquals($this->di->make('foo'), 'bar');

        require_once __DIR__ . '/Bar.php';
        require_once __DIR__ . '/Foo.php';

        $this->di->bind('Bar', 'Foo');
        $this->assertEquals('Foo', get_class($this->di->make('Bar')));
        $this->di->forget('Bar');

        $this->di->bind('Bar', function($di){
            return $di->make('Foo');
        });
        $this->assertEquals('Foo', get_class($this->di->make('Bar')));
    }

    public function testSingleton()
    {
        require_once __DIR__ . '/Bar.php';
        require_once __DIR__ . '/Foo.php';

        $this->di->singleton('foo', function() {
            return new \Foo;
        });

        $this->assertEquals(
            $this->di->make('foo'),
            $this->di->make('foo')
        );

        $this->di->singleton('dt','DateTime');

        $this->di->when('DateTimeZone')->needs('$timezone')->give('UTC');
        
        $this->assertEquals(
            $this->di->make('dt'),
            $this->di->make('dt')
        );

        $this->assertTrue($this->di->make('dt') instanceof \DateTime);

        try {
            $this->di->singleton('bar', 0);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof ContainerException);
        }
    }

    public function testAutowiring()
    {
        $di = $this->di;

        $di->instance('x', 6);
        $di->instance('y', 2);
        $di->instance('z', 3);

        $di->bind('foo', function($di) {
            return (
                $di->make('x') * $di->make('y') / $di->make('z')
            );
        });

        $this->assertEquals(4, $di->make('foo'));
    }

    public function testPreventDuplicate()
    {
        $this->di->bind('foo', 'Bar');

        try {
            $this->di->bind('foo', 'Baz');
        } catch (Exception $e) {
            $this->assertEquals(true, $e instanceof ContainerException);
        }
    }

    public function testAlias()
    {
        $this->di->bind('DataBase', function(){
            return 'MongoDB';
        });

        $this->di->alias('db', 'DataBase');

        $this->assertEquals('MongoDB', $this->di->make('db'));
    }

    public function testDecorator()
    {
        $thisis = $this;

        $this->di->instance('foo', 7);

        $this->di->decorator('foo', function($old, $self) use ($thisis) {
            $thisis->assertEquals(7, $old);
            $this->assertEquals(true, $self instanceof Di);

            return pow($old, 2);
        });

        $thisis->assertEquals(49, $this->di->make('foo'));
    }

    public function testFork()
    {
        $thisis = $this;

        $this->di->instance('foo', new \Foo);

        $this->di->fork('foo', function($foo, $di) use ($thisis) {
            $thisis->assertEquals(true, $foo instanceof \Foo);
            $thisis->di->instance('x-foo', $foo);
        });

        $this->assertEquals(true, $this->di->make('foo') == $this->di->make('x-foo'));
        $this->assertEquals(false, $this->di->make('foo') === $this->di->make('x-foo'));
    }

    public function testReflection()
    {
        require_once __DIR__ . '/Foo.php';
        require_once __DIR__ . '/Baz.php';
        require_once __DIR__ . '/Quux.php';

        $fn = function(\Baz $baz) {
            return $baz->getFoo();
        };

        $baz = $this->di->make('Baz');

        $this->assertEquals(true, $baz instanceof \Baz);
        $this->assertEquals(true, $baz->getFoo() instanceof \Foo);
        $this->assertEquals(true, $this->di->make([$baz,'quux']) instanceof \Quux);
        $this->assertEquals(true, $this->di->make('Baz::quux') instanceof \Quux);
        $this->assertEquals(true, $this->di->make($baz) instanceof \Quux);
        $this->assertEquals(true, $this->di->make([$baz,'staticQuux']) instanceof \Quux);
        $this->assertEquals(true, $this->di->make('Baz::staticQuux') instanceof \Quux);
        $this->assertEquals(true, $this->di->make($fn) instanceof \Foo);

        $foo = new \Foo;
        $quux = new \Quux;

        $this->di->instance('foo', $foo);
        $this->di->instance('quux', $quux);

        $this->assertEquals([$foo,$quux], $this->di->makes('foo','quux'));
        $this->assertEquals([$foo,$quux], $this->di->makes(['foo','quux']));
    }

    public function testContext()
    {
        date_default_timezone_set('UTC');
        $date = '2007-05-25';
        $ts   = 1180051200;

        $this->di->when('DateTime')->needs('$time')->give($date);
        $this->di->when('DateTime')->needs('$datetime')->give($date);
        $this->di->when('DateTimeZone')->needs('$timezone')->give('UTC');
        
        $this->assertEquals($ts, $this->di->make('DateTime')->getTimestamp());
        $this->assertEquals($date, $this->di->make('DateTime')->format('Y-m-d'));

        require_once __DIR__ . '/Bar.php';
        require_once __DIR__ . '/Foo.php';
        require_once __DIR__ . '/Bat.php';

        $this->di->when('Bat')
                 ->needs('Bar')
                 ->give(function($di){
                    return $di->make('Foo');
                });

        $this->assertEquals(true, $this->di->make('Bat')->getBar() instanceof \Foo);

        require_once __DIR__ . '/Quux.php';

        $quux = new \Quux;

        $this->di->when('Baz')
                 ->needs('Quux')
                 ->give($quux);

        $this->assertEquals($quux, $this->di->make('Baz::quux'));
    }

    public function testAdditional()
    {
        date_default_timezone_set('UTC');
        $date = '2007-05-25';
        $ts   = 1180051200;
        
        $this->di->when('DateTimeZone')->needs('$timezone')->give('UTC');
        
        $dateTime = $this->di->make('DateTime', ['time' => $date]);

        $this->assertEquals($ts, $dateTime->getTimestamp());
        $this->assertEquals($date, $dateTime->format('Y-m-d'));

        $fn = function($x, $y) {
            return $x * $y;
        };

        $mul = $this->di->make($fn, [
            'x' => 2,
            'y' => 3
        ]);

        $this->assertEquals(6, $mul);

        require_once __DIR__ . '/Foo.php';

        $foo = new \Foo;

        $baz = $this->di->make('Baz', [
            'foo' => $foo,
        ]);

        $this->assertEquals($foo, $baz->getFoo());
    }

    public function testService()
    {
        require_once __DIR__ . '/../../../src/Reservoir/ServiceProvider.php';
        require_once __DIR__ . '/Service.php';

        spl_autoload_register(function($class){
            if ($class === 'DeferredService') {
                require_once __DIR__ . '/DeferredService.php';
            }
        });

        $this->di->register('Service');
        $this->di->register('DeferredService');

        $this->assertEquals(true,$this->di->make('xfoo') instanceof \Foo);

        $this->assertEquals(false, $this->di->has('xbaz'));
        $this->assertEquals(true,$this->di->make('xbaz') instanceof \Baz);
    }

    public function testResolving()
    {
        $thisis = $this;

        $this->di->instance('foo', 'bar');
        $this->di->instance('bar', 'baz');

        $this->di->resolving('foo', function($val, $context) use ($thisis) {
            $thisis->assertEquals($val, 'bar');
            $thisis->assertEquals($context, $thisis->di);
        });

        $this->di->resolving(function($val, $context) use ($thisis) {
            $thisis->assertContains($val, ['bar','baz']);
            $thisis->assertEquals($context, $thisis->di);
        });

        $this->di->make('foo');
        $this->di->make('bar');
    }

    public function testDefaults()
    {
        $this->assertEquals(100,$this->di->make('Foo::defaults'));
        $this->assertEquals(50,$this->di->make('Foo::defaults',['a' => 50]));
    }
}
