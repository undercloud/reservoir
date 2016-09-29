<?php
namespace Reservoir;

/**
 * HashMap key-value storage
 */
class PersistentStorage
{
    /**
     * @var array
     */
    protected static $storage = [];

    /**
     * @var array
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
        foreach (self::$storage as $key => $value) {
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
        foreach (self::$storage as $index => $value) {
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
        foreach (self::$storage as $index => $value) {
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
        self::$storage = [];
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
        if (!isset(self::$storage[$key])) {
            self::$storage[$key] = new HashMap;
        }

        return self::$storage[$key];
    }
}
?>