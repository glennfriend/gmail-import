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
        pr("PHP Version need >= 5.5");
        exit;
    }

    if (!getParam('exec')) {
        pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
    }
    Lib\Log::record('start PHP '. phpversion() );

    //
    $mail = Lib\Gmail::getEmailsNotSettingRead();
    if ($error = Lib\Gmail::getError()) {
        pr($error, true);
        exit;
    }

    pr($mail);


    pr("done", true);
}


