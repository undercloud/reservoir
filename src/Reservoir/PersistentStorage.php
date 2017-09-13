<?php
namespace Reservoir;

/**
 * HashMap key-value storage
 *
 * @category IoC\DI
 * @package  Reservoir
 * @author   undercloud <lodashes@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     http://github.com/undercloud/reservoir
 */
class PersistentStorage
{
    /**
     * @var array Reservoir\HashMap instances
     */
    protected $storage = [];

    /**
     * @var array watch keys
     */
    protected static $watch = ['aliases','instances','registry','singletones'];

    /**
     * Check if key exists
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function has($key)
    {
        return in_array($key, $this->keys(), true);
    }

    /**
     * Return all keys
     *
     * @return array
     */
    public function keys()
    {
        $keys = [];
        foreach ($this->storage as $key => $value) {
            if (in_array($key, self::$watch)) {
                $keys = array_merge($keys, $value->keys());
            }
        }

        return $keys;
    }

    /**
     * Remove storage value by key
     * Return true if value is deleted, otherwise return false
     *
     * @param string $key key
     *
     * @return boolean
     */
    public function forget($key)
    {
        foreach ($this->storage as $index => $value) {
            if (in_array($index, self::$watch)) {
                if ($value->has($key)) {
                    $value->del($key);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return source reference
     *
     * @param string $key key
     *
     * @return Reservoir\HashMap
     */
    public function getSourceReference($key)
    {
        foreach ($this->storage as $index => $value) {
            if (in_array($index, self::$watch)) {
                if ($value->has($key)) {
                    return $value;
                }
            }
        }
    }

    /**
     * Clear storage
     *
     * @return void
     */
    public function flush()
    {
        $this->storage = [];
    }

    /**
     * Magic __get
     *
     * @param string $key key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (!isset($this->storage[$key])) {
            $this->storage[$key] = new HashMap;
        }

        return $this->storage[$key];
    }
}
