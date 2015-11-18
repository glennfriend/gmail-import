<?php
/**
 *  Config Manager
 */
class Config
{

    /**
     *
     */
    static protected $cf = array();

    /**
     *
     */
    static public function init( $configPath )
    {
        if ( !file_exists($configPath) ) {
            return false;
        }

        foreach (glob("{$configPath}/*.php") as $file) {
            $filename = basename($file);
            $key = substr( $filename, 0, strlen($filename)-4 );
            self::$cf[$key] = include($file);
        }

        if ( !self::$cf ) {
            return false;
        }
        return true;
    }

    /**
     *  同 soft
     *  如果資料不存在 或是值為 null, 直接顯示錯誤訊息
     *
     *  @see Config::get()
     *  @param int|string - $key
     *  @return any
     */
    static public function get( $key )
    {
        $value = self::soft($key);
        if ( null === $value ) {
            throw new Exception("Error: config [{$key}] not found!");
        }
        return $value;
    }

    /**
     *  使用 '.' 符號的方式取得陣列中的資料
     *
     *  example:
     *
     *      get('vivian')          -> $array['vivian'], 若無值, 則傳回 null
     *      get('vivian', 'guest') -> $array['vivian'], 若無值, 則傳回 'guest' string
     *      get('vivian.age')      -> $array['vivian']['age']
     *      get('vivian.0')        -> $array['vivian'][0]
     *
     *  @see    laravel array_get
     *  @param  int|string - $key
     *  @param  any        - $default
     *  @return any
     */
    static public function soft( $key, $default=null )
    {
        $data = self::$cf;
        if (is_null($key)) {
            return $default;
        }
        if (isset($data[$key])) {
            return $data[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($data) or ! array_key_exists($segment, $data)) {
                return $default;
            }
            $data = $data[$segment];
        }
        return $data;
    }


}
