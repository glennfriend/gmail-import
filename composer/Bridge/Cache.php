<?php
namespace Bridge;
use Doctrine;

/**
 *
 */
class Cache
{

    /**
     *  cache
     */
    private static $cache = array();

    /**
     *  init
     */
    public static function init($cachePath)
    {
        self::$cache = new Doctrine\Common\Cache\FilesystemCache($cachePath);
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  get cache
     */
    public static function get($key)
    {
        return self::$cache->fetch($key);
    }

    /*
    public static function has( $key ) {}
    */

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  set cache
     */
    public static function set($key, $value)
    {
        self::$cache->save($key, $value);
    }

    // public static function forever 無時間限制的快取

    /**
     *  remove cache
     */
    public static function remove($key)
    {
        self::$cache->delete( $key );
    }

    /**
     *  remove cache by prefix
     *  移除該值開頭的所有快取
     */
    public static function removePrefix($prefix)
    {
        self::$cache->deleteByPrefix($key);
    }

    /**
     *  clean all cache data
     */
    public static function flush()
    {
        self::$cache->deleteAll();
    }

}
