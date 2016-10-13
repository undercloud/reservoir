<?php
namespace Reservoir\Tests;

use Exception;
use Reservoir\Di;
use Reservoir\ContainerException;
use PHPUnit_Framework_TestCase;

class ReservoirTest extends PHPUnit_Framework_TestCase
{
    private $di;

    public function __construct()
    {
        error_reporting(-1);
        $this->di = new Di;
    }

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

        try {
            $this->di->singleton('bar', 0);
        } catch (Exception $e) {
            $this->assertEquals(true, $e instanceof ContainerException);
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

        $this->assertEquals(true, $this->di->isAlias('db'));
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

    public function testArrayAccess()
    {
        $this->di['foo'] = 'bar';

        $this->assertEquals('bar', $this->di['foo']);
        $this->assertEquals(true, isset($this->di['foo']));

        unset($this->di['foo']);
        $this->assertEquals(false, isset($this->di['foo']));
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
        $this->assertEquals(true, $this->di->make($fn) instanceof \Foo);
    }

    public function testContext()
    {
        $date = '2007-05-25';
        $ts   = 1180033200;

        $this->di->when('DateTime')
                 ->needs('$time')
                 ->give($date);


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
    }

    public function testAdditional()
    {
        $date = '2007-05-25';
        $ts   = 1180033200;

        $dateTime = $this->di->make('DateTime', [
            'time' => $date
        ]);

        $this->assertEquals($ts, $dateTime->getTimestamp());
        $this->assertEquals($date, $dateTime->format('Y-m-d'));


    }

    public function testService()
    {

    }
}