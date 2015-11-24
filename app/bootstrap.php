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
}

function di()
{
    static $container;
    if ($container) {
        return $container;
    }

    $container = new DependencyInjection\ContainerBuilder();
    return $container;
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
            di()->get('log')->record(
                print_r($data, true)
            );
        }
    }
    else {
        echo $data;
        echo "\n";

        if ($writeLog) {
            di()->get('log')->record($data);
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
