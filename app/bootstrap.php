<?php

function initialize($basePath)
{
    error_reporting(E_ALL);
    ini_set('html_errors','Off');
    ini_set('display_errors','On');

    require_once  $basePath . '/app/library/Config.php';
    Config::init( $basePath . '/app/config');

    if ( conf('public.base.path') !== $basePath ) {
       show('base path setting error!');
       exit;
    }

    date_default_timezone_set(conf('app.timezone'));


    require_once $basePath . '/app/library/Log.php';
    Log::init(   $basePath . '/var');

    require_once $basePath . '/composer/vendor/autoload.php';

}

function conf($key)
{
    return Config::get($key);
}

function show($data, $writeLog=false )
{
    if (is_object($data) || is_array($data)) {
        print_r($data);
    }
    else {
        echo $data;
        echo "\n";
    }

    if ($writeLog) {
        Log::record($data);
    }

}

/**
 *  get command line param or get web param
 *  
 *  @dependency isCli()
 *  @dependency getWebParam()
 *  @dependency getCliParam()
 */
function getParam($key)
{
    if (isCli()) {
        return getCliParam($key);
    }
    else {
        return getWebParam($key);
    }
}

function getWebParam($key)
{
    if (isset($_POST[$key])) {
        return $_POST[$key];
    }
    elseif (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return null;
}

/**
 *  get command line value
 *
 *  @return string|int or null
 */
function getCliParam($key)
{
    global $argv;
    $allParams = $argv;
    array_shift($allParams);

    if (in_array($key, $allParams)) {
        return true;
    }

    foreach ($allParams as $param) {

        $tmp = explode('=', $param);
        $name = $tmp[0];
        array_shift($tmp);
        $value = join('=', $tmp);

        if ($name===$key) {
            return $value;
        }
    }

    return null;
}

function isCli()
{
    return PHP_SAPI === 'cli';
}
