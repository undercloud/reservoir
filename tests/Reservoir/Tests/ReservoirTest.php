<?php
namespace Reservoir\Tests;

use Reservoir\Di;
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

        $this->assertEquals(
            $this->di->make('foo'),
            'bar'
        );
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

        $this->assertEquals('Foo', $this->di->make(function(\Bar $bar){
            return get_class($bar);
        }));
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
    }

    public function testAutowiring()
    {
        $di = $this->di;

        $di->instance('x', 6);
        $di->instance('y', 2);
        $di->instance('z', 3);

        $di->bind('foo', function() use ($di) {
            return (
                $di->make('x') * $di->make('y') / $di->make('z')
            );
        });

        $this->assertEquals(4, $di->make('foo'));
    }
}
