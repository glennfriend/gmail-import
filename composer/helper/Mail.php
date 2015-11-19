<?php

namespace Helper;

use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;

class Mail
{

    public static function sendSuccess()
    {
        $projectName = conf('app.name');
        $subject = "[{$projectName}] success - ". date("Y-m-d H:i:s");
        $body    = "";
        self::send($subject, $body);
    }

    public static function sendFail()
    {
        $subject = "[{$projectName}] fail - ". date("Y-m-d H:i:s");
        $body    = "";
        self::send($subject, $body);
    }

    /* --------------------------------------------------------------------------------
        private
    -------------------------------------------------------------------------------- */

    private static function send($subject, $body)
    {
        $projectName = conf('app.name');
        $mail = new Message;
        $mail
            ->setFrom("{$projectName} <localhost@localhost.com>")
            // ->addTo('your email')
            // ->addTo('your email')
            // ->addTo('your email')
            ->setSubject($subject)
            ->setBody($body);

        $mailer = new SendmailMailer;
        $mailer->send($mail);
    }

}

