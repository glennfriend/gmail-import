<?php
namespace Bridge\Options;
use Doctrine;

class CacheFile
{
    protected $cache;

    /**
     *  init
     */
    public function __construct($cachePath)
    {
        $this->cache = new Doctrine\Common\Cache\FilesystemCache($cachePath);
    }

    /* --------------------------------------------------------------------------------
        access
    -------------------------------------------------------------------------------- */

    /**
     *  get cache
     */
    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    /* --------------------------------------------------------------------------------
        write
    -------------------------------------------------------------------------------- */

    /**
     *  set cache
     */
    public function set($key, $value)
    {
        $this->cache->save($key, $value);
    }

    /**
     *  remove cache
     */
    public function remove($key)
    {
        $this->cache->delete($key);
    }

    /**
     *  remove cache by prefix
     *  移除該值開頭的所有快取
     */
    public function removePrefix($prefix)
    {
        $this->cache->deleteByPrefix($key);
    }

    /**
     *  clean all cache data
     */
    public function flush()
    {
        $this->cache->deleteAll();
    }

}
