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
    $mails = Lib\Gmail::getEmails(2);
    if ($error = Lib\Gmail::getError()) {
        pr($error, true);
        exit;
    }

    $inboxes = new Inboxes();
    foreach ($mails as $mailInfo) {
        $inbox = makeInbox($mailInfo);
        $result = $inboxes->addInbox($inbox);
        if ($result) {
            pr("add success, inbox id = " . $result, true);
        }
        else {
            pr("add error, gmail id = " . $mailInfo['gmail_id'], true);
        }
    }

    pr("done", true);
}

function makeInbox($info)
{
    $from    = (array) $info['from'][0];
    $replyTo = (array) $info['reply_to'][0];
    $to      = (array) $info['to'][0];

    $inbox = new Inbox();
    $inbox->setGmailId          ( $info['gmail_id']                             );
    $inbox->setFromEmail        (    $from['mailbox'] .'@'.    $from['host']    );
    $inbox->setToEmail          (      $to['mailbox'] .'@'.      $to['host']    );
    $inbox->setReplyToEmail     ( $replyTo['mailbox'] .'@'. $replyTo['host']    );
    $inbox->setSubject          ( $info['subject']                              );
    $inbox->setContent          ( $info['content']                              );
    $inbox->setEmailCreateTime  ( strtotime($info['date'])                      );

    $inbox->setProperty('info', [
        'from'      => $info['from'],
        'reply_to'  => $info['reply_to'],
        'to'        => $info['to'],
        'date'      => $info['date'],
        'content'   => $info['content'],
    ]);
    return $inbox;
}
