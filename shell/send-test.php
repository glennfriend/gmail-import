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
 *  寄一封測試信給自己
 */
function perform()
{
    if (!getParam('exec')) {
        pr('---- debug mode ---- (你必須要輸入參數 exec 才會真正執行)');
        exit;
    }

    $sendToEmail = conf('gmail.email');
    $mail = new Nette\Mail\Message;
    $mail
        ->setFrom($sendToEmail)
        ->addTo($sendToEmail)
        ->setSubject('[gmail-import] just test')
        ->setBody("Hello World.")
    ;

    $mailer = new Nette\Mail\SmtpMailer([
        'host'      => 'smtp.gmail.com',
        'username'  => conf('gmail.email'),
        'password'  => conf('gmail.passwd'),
        'secure'    => 'ssl',
    ]);
    $mailer->send($mail);

}
