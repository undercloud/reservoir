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
        if ($this->has($offset)) {
            return $this->hashMap[$offset];
        }
    }

    /**
     * Set container value associated with key
     *
     * @param string $offset key
     * @param mixed  $value  value
     *
     * @return null
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
     * @return null
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
     * @return null
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
     * @see Reservoir\HashMap::has
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @see Reservoir\HashMap::get
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @see Reservoir\HashMap::set
     *
     * @return null
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @see Reservoir\HashMap::del
     *
     * @return null
     */
    public function offsetUnset($offset)
    {
        $this->del($offset);
    }
}
?>