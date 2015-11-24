<?php

namespace Lib;

class Log
{

    protected static $_logPath = null;

    /**
     *  init
     */
    public static function init( $logPath )
    {
        self::$_logPath = $logPath;
    }

    /**
     *  error log
     */
    public static function getPath()
    {
        if (null === self::$_logPath) {
            die('error - 6324734905763409573498546345634853453429403');
        }

        return self::$_logPath;
    }

    /**
     *  system log
     */
    public static function record( $content )
    {
        $content = date("Y-m-d H:i:s") . ' - '. $content;
        self::write( 'system.log', $content );
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    /**
     *  write file
     */
    public static function write( $file, $content )
    {
        if (!preg_match('/^[a-z0-9_\-\.]+$/i', $file)) {
            return;
        }

        $filename = self::getPath() .'/'. $file;
        file_put_contents( $filename, $content."\n", FILE_APPEND );
    }

}
