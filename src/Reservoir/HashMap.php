<?php
namespace Reservoir\Di;

use Countable;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

class HashMap implements ArrayAccess, IteratorAggregate, Countable
{
    protected $hashMap = [];

    public function __construct(array $array = [])
    {
        $this->hashMap = $array;
    }

    public function get($offset)
    {
        if ($this->has($offset)) {
            return $this->hashMap[$offset];
        }
    }

    public function set($offset, $value)
    {
        $this->hashMap[$offset] = $value;
    }

    public function has($offset)
    {
        return array_key_exists($offset, $this->hashMap);
    }

    public function del($offset)
    {
        unset($this->hashMap[$offset]);
    }

    public function keys()
    {
        return array_keys($this->hashMap);
    }

    public function values()
    {
        return array_values($this->hashMap);
    }

    public function toArray()
    {
        return $this->hashMap;
    }

    public function clear()
    {
        $this->hashMap = [];
    }

    public function getIterator()
    {
        return new ArrayIterator($this->hashMap);
    }

    public function count()
    {
        return count($this->hashMap);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->del($offset);
    }
}
?>