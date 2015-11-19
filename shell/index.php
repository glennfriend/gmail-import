#!/usr/bin/env php -q
<?php

if (PHP_SAPI !== 'cli') {
    exit;
}

$basePath = dirname(__DIR__);
require_once $basePath . '/app/bootstrap.php';
initialize($basePath);

perform();
exit;

// --------------------------------------------------------------------------------
// 
// --------------------------------------------------------------------------------

/**
 * 
 */
function perform()
{
    if ( phpversion() < '5.5' ) {
        show("PHP Version need >= 5.5");
        exit;
    }

    if (!getParam('exec')) {
        show('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
    }

    // test only
    $i = new Inbox();
    print_r($i);

    Lib\Log::record('start PHP '. phpversion() );
    // xxxx();

    show("done", true);
}
