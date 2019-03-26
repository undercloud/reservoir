<?php
namespace Reservoir;

use Closure;
use InvalidArgumentException;

/**
 * Events resolver
 *
 * @method void resolving(Closure $callback)
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class Pipe
{
    /**
     * @var array
     */
    private $bindings = [];

    /**
     * Add container listener
     *
     * @param  string  $key      name
     * @param  Closure $callback handle
     *
     * @return void
     */
    public function on($key, Closure $callback)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Argument 1 must be string, %s given',
                    gettype($key)
                )
            );
        }

        if (!isset($this->bindings[$key])) {
            $this->bindings[$key] = [];
        }

        $this->bindings[$key][] = $callback;
    }

    /**
     * Add container listener
     *
     * @param  Closure $callback handle
     *
     * @return void
     */
    public function all(Closure $callback)
    {
        $this->on('*', $callback);
    }

    /**
     * Fire container listener
     *
     * @param  string    $key       name
     * @param  mixed     $val       item
     * @param  Container $container instance
     *
     * @return void
     */
    public function fire($key, $val, Container $container)
    {
        foreach ($this->bindings as $bind => $callbacks) {
            if ($key === $bind or $bind === '*') {
                foreach ($callbacks as $callback) {
                    call_user_func($callback, $val, $container);
                }
            }
        }
    }
}
