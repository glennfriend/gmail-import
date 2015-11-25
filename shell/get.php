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
    $params = getParams();
    if (!isset($params[0])) {
        pr('----------------------------------------------------- [help]');
        pr('代入 參數 表示取得哪一筆資料');
        pr('    example:');
        pr('        $ php get.php 10');
        pr('        $ php get.php last');
        pr('------------------------------------------------------ [end]');
        exit;
    }

    $inboxes = new Inboxes();

    // get by last
    if ('last'===$params[0]) {
        $myInboxes = $inboxes->findInboxes([
            '_order'        => 'id,DESC',
            '_itemsPerPage' => 1
        ]);
        if (isset($myInboxes[0])) {
            show($myInboxes[0]);
        }
        else {
            pr('Inbox not found');
        }
        exit;
    }

    // get by id
    $id = (int) $params[0];
    if (!$id) {
        exit;
    }

    $inbox = $inboxes->getInbox($id);
    if (!$inbox) {
        pr('Inbox not found');
        exit;
    }
    show($inbox);

}

function show($inbox)
{
    pr('------------------------------------------------------------');
    pr('id:      ' . $inbox->getId()        );
    pr('from:    ' . $inbox->getFromEmail() );
    pr('to:      ' . $inbox->getToEmail()   );
    pr('subject: ' . $inbox->getSubject()   );
    pr('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
    pr($inbox->getContent());
    pr('<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

    $info = $inbox->getProperty('info');
    if (isset($info['file_attachments']) && count($info['file_attachments'])>0) {
        $count = count($info['file_attachments']);
        pr("---------- attachments x {$count} ----------");
        foreach ($info['file_attachments'] as $attachment) {
            pr($attachment['name']);
        }
    }

    pr('');

}
