<?php
namespace Bridge\Tie;

interface Cache
{
    public static function init($cachePath);

    public static function get($key);
  //public static function has($key);

    public static function set($key, $value);
    public static function remove($key);
    public static function removePrefix($prefix);
    public static function flush();
}
