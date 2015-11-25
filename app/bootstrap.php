<?php
use Symfony\Component\DependencyInjection;


function initialize($basePath)
{
    error_reporting(E_ALL);
    ini_set('html_errors','Off');
    ini_set('display_errors','On');

    if ( phpversion() < '5.5' ) {
        pr("PHP Version need >= 5.5");
        exit;
    }

    require_once $basePath . '/composer/vendor/autoload.php';

    // init config
    Lib\Config::init( $basePath . '/app/config');
    if ( conf('app.path') !== $basePath ) {
       pr('base path setting error!');
       exit;
    }

    date_default_timezone_set(conf('app.timezone'));
    diInit();
}

function conf($key)
{
    return Lib\Config::get($key);
}

function pr($data, $writeLog=false)
{
    if (is_object($data) || is_array($data)) {
        print_r($data);

        if ($writeLog) {
            di('log')->record(
                print_r($data, true)
            );
        }
    }
    else {
        echo $data;
        echo "\n";

        if ($writeLog) {
            di('log')->record($data);
        }
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


/**
 *  包裝了 Symfony Dependency-Injection
 *  提供了簡易的取用方式 DI->get( $getParam )
 */
function di($getParam=null)
{
    static $container;
    if ($container) {
        if ($getParam) {
            return $container->get($getParam);
        }
        return $container;
    }

    $container = new DependencyInjection\ContainerBuilder();
    return $container;
}

/**
 *
 */
function diInit()
{
    $di = di();

    // init log folder
    $di->setDefinition('log', new DependencyInjection\Definition(
        'Lib\Log'
    ));
    $di->get('log')->init( conf('app.path') . '/var' );

    // init email temp folder
    $di->setDefinition('gmail', new DependencyInjection\Definition(
        'Lib\Gmail'
    ));
    $di->get('gmail')->init([
        'temp_path' => conf('app.path') . '/var',
    ]);

    // init cache
    //$di->register('cache', 
    /*
    $di->setDefinition('cache', new DependencyInjection\Definition(
        'Illuminate\Cache\CacheManager',
        [
            'config' => [
                'cache.driver'  => 'file',
                'cache.path'    => conf('app.path') . '/var/cache',
                'cache.prefix'  => 'cache_',
            ]
        ]
    ));
    $di->get('cache')->driver('file');
    */
/*
    $cacheManager = new Illuminate\Cache\CacheManager([
        'files' => 'file',
        'config' => [
            'cache.default' => 'files',
            'cache.stores.files' => [
                'driver'  => 'file',
                'path'    => conf('app.path') . '/var/cache',
            ],
//            'cache.driver'  => 'file',
            'cache.path'    => conf('app.path') . '/var/cache',
            'cache.prefix'  => 'cache_',
        ]
    ]);
    pr($cacheManager);
    pr($cacheManager->driver());
*/

    $cachePath = conf('app.path') . '/var/cache';
    $di->setDefinition('cache', new DependencyInjection\Definition(
        'Bridge\Cache'
    ));
    $di->get('cache')->init($cachePath);



    //$cacheDriver = new Doctrine\Common\Cache\FilesystemCache(conf('app.path') . '/var/cache');

    //pr($cacheDriver);

}



