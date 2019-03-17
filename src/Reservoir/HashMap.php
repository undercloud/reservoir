<?php
namespace Reservoir;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * Key-value storage like  Java HashMap
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class HashMap implements ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array container
     */
    protected $hashMap = [];

    /**
     * Initialize instance
     *
     * @param array $array predefined values
     */
    public function __construct(array $array = [])
    {
        $this->hashMap = $array;
    }

    /**
     * Return value associated with key
     *
     * @param string $offset key
     *
     * @return mixed
     */
    public function get($offset)
    {
        return ($this->has($offset))
            ? $this->hashMap[$offset]
            : null;
    }

    /**
     * Set container value associated with key
     *
     * @param string $offset key
     * @param mixed  $value  value
     *
     * @return void
     */
    public function set($offset, $value)
    {
        $this->hashMap[$offset] = $value;
    }

    /**
     * Check if key exists
     *
     * @param string $offset key
     *
     * @return boolean
     */
    public function has($offset)
    {
        return array_key_exists($offset, $this->hashMap);
    }

    /**
     * Remove value by key
     *
     * @param string $offset key
     *
     * @return void
     */
    public function del($offset)
    {
        unset($this->hashMap[$offset]);
    }

    /**
     * Return all keys from storage
     *
     * @return array
     */
    public function keys()
    {
        return array_keys($this->hashMap);
    }

    /**
     * Return all values from storage
     *
     * @return array
     */
    public function values()
    {
        return array_values($this->hashMap);
    }

    /**
     * Clear storage
     *
     * @return void
     */
    public function clear()
    {
        $this->hashMap = [];
    }

    /**
     * Return ArrayIterator instance
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->hashMap);
    }

    /**
     * Count items in storage
     *
     * @return integer
     */
    public function count()
    {
        return count($this->hashMap);
    }

    /**
     * @see HashMap::has
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @see HashMap::get
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @see HashMap::set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @see HashMap::del
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->del($offset);
    }
}
