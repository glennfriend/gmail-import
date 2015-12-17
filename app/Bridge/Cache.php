<?php
namespace Bridge;

/**
 *
 */
class Cache implements Tie\Cache
{

    /**
     *  cache
     */
    protected static $cache = array();

    /**
     *  init
     */
    public static function init($cachePath)
    {
        self::$cache = new Options\CacheFile($cachePath);
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  get cache
     */
    public static function get($key)
    {
        return self::$cache->get($key);
    }

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  set cache
     */
    public static function set($key, $value)
    {
        self::$cache->set($key, $value);
    }

    /**
     *  remove cache
     */
    public static function remove($key)
    {
        self::$cache->remove($key);
    }

    /**
     *  remove cache by prefix
     *  移除該值開頭的所有快取
     */
    public static function removePrefix($prefix)
    {
        self::$cache->removePrefix($key);
    }

    /**
     *  clean all cache data
     */
    public static function flush()
    {
        self::$cache->flush();
    }

}
