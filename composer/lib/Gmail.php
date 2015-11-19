<?php

namespace Lib;
use Lib;

/**
 *  Gmail Manager
 *
 *      該程式會取得 信件訊息
 *      並將信件設為 已讀
 *
 */
class Gmail
{
    /**
     *  error message
     */
    protected static $errorMessage = null;

    /**
     *  取得最舊的 未讀信件
     *      - 讀取信件後, imap_fetchbody() 會將信件狀態改為 已讀
     *
     *  @param int $number  - 取得多少封信件
     *  @return array
     */
    public static function getEmails($num=10)
    {
        return self::_accessEmails($num, true);
    }

    /**
     *  同 getEmails
     *  但是不會將信件設定為已讀
     *  該程式可以做為測試使用
     *
     *  @see getEmails()
     */
    public static function getEmailsNotSettingRead($num=10)
    {
        return self::_accessEmails($num, false);
    }

    /**
     *  取得錯誤訊息
     *
     *  @return null or string - error message
     */
    public static function getError()
    {
        return self::$errorMessage;
    }

    // --------------------------------------------------------------------------------
    // private
    // --------------------------------------------------------------------------------

    /**
     *  取得最舊的一封 未讀信件
     *      - 讀取信件後, imap_fetchbody() 會將信件狀態改為 已讀
     *
     *  @see http://tw2.php.net/manual/en/function.imap-search.php
     *  @param int $number  - 取得多少封信件
     *  @return array
     */
    private static function _accessEmails($num, $isSettingRead)
    {
        self::$errorMessage = null;
        $num = 0;
        $num = 2;

        $inbox = self::_getInboxes();
        if (!$inbox) {
            return [];
        }

        $emails = imap_search($inbox, 'UNSEEN');
        $readKey = ( $isSettingRead ? 0 : FT_PEEK );

        $i = 0;
        $infos = [];
        foreach ($emails as $id) {
            if ($i >= $num) {
                break;
            }
            $i++;

            $headerInfo = imap_headerinfo($inbox, $id);
            $infos[] = [
                'gmail_id'  => $id,
                'subject'   => $headerInfo->subject,
                'from'      => $headerInfo->from,
                'reply_to'  => $headerInfo->reply_to,
                'to'        => $headerInfo->to,
                'date'      => $headerInfo->MailDate,
                'content'   => quoted_printable_decode(imap_fetchbody($inbox, $id, 1, $readKey)),
            ];
        }

        self::$errorMessage = null;
        return $infos;
    }

    /**
     *  always get one
     *  @return imap_open resource
     */
    private static function _getInboxes()
    {
        static $inbox;
        if ($inbox) {
            return $inbox;
        }

        if (!function_exists('imap_open')) {
            self::$errorMessage = "imap_open library not found";
            return [];
        }

        $hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
        $email    = Config::get('gmail.email');
        $password = Config::get('gmail.passwd');

        ob_start();
            $inbox = imap_open($hostname, $email, $password);
        ob_end_clean();

        if ($errors = imap_errors()) {
            self::$errorMessage = 'imap_open() Error: ' . join("\n", $errors);
            return [];
        }

        return $inbox;
    }

    /**
     *
     */
    private static function close()
    {
        $inbox = self::_getInboxes();
        imap_close($inbox);
    }

}
