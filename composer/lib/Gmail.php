<?php

namespace lib;

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
     *  放置 email 附件的目錄
     */
    protected static $attachPath = null;

    /**
     *
     */
    public static function init(Array $options)
    {
        if (isset($options['attach_path'])) {
            self::$attachPath = $options['attach_path'];
        }
    }

    /**
     *  取得最舊的 未讀信件
     *      - 讀取信件後, 會將信件狀態改為 已讀
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
     *  取得最舊的 未讀信件
     *
     *  @see http://tw2.php.net/manual/en/function.imap-search.php
     *  @param int $number  - 取得多少封信件
     *  @return array
     */
    private static function _accessEmails($num, $isSettingRead)
    {
        self::$errorMessage = null;

        $inbox = self::_getInboxes();
        if (!$inbox) {
            return [];
        }

        $emails = imap_search($inbox, 'UNSEEN');
        if (!$emails) {
            return [];
        }

        $i = 0;
        $infos = [];
        foreach ($emails as $id) {
            if ($i >= $num) {
                break;
            }
            $i++;


            //
            $headerInfo = imap_headerinfo($inbox, $id);
            //pr($headerInfo); exit;
            //pr(imap_fetchstructure ($inbox, $id));

            $bodyText = imap_body($inbox, $id, FT_PEEK);
            list($bodyHeader, $body) = self::parseBody($bodyText, $headerInfo->message_id);

            $infos[] = [
                'message_id'        => $headerInfo->message_id,
                'subject'           => $headerInfo->subject,
                'from'              => $headerInfo->from,
                'reply_to'          => $headerInfo->reply_to,
                'to'                => $headerInfo->to,
                'date'              => $headerInfo->MailDate,
                //'content'           => $content,
                //'content_version'   => $imapVersion,
                'body_header'       => $bodyHeader,
                'body'              => htmlspecialchars($body),
            ];

            // 設定為已讀    TODO: 請改用其它方式
            if ($isSettingRead) {
                imap_body($inbox, $id, 0);
            }

        }

        self::$errorMessage = null;
        self::close();
        return $infos;
    }

    /**
     *  parse body
     *
     *  @see https://github.com/php-mime-mail-parser/php-mime-mail-parser
     *  @return information array
     */
    private static function parseBody($body, $id)
    {
        static $parser;
        if (!$parser) {
            $parser = new \PhpMimeMailParser\Parser();
        }


        $parser->setText($body);
        // pr($parser); exit;

        // 附件
        if (self::$attachPath) {
            $folderId = md5($id);
            $path = self::$attachPath . "/var/attach/{$folderId}/";
            $parser->saveAttachments($path);
            $attachments = $parser->getAttachments();
            //pr($attachments);
        }

        $headers = $parser->getHeaders();
        $body    = $parser->getMessageBody();
        $body    = self::_minusBodyContent($body);

        return [
            $headers,
            $body,
        ];
    }

    /**
     *  解析出來的內容不夠單純
     *  試著找出一組像 "--001a113d414844af3e0524dc7f0f" 的文字
     *  該字串後面的值都移除
     */
    private static function _minusBodyContent($body)
    {
        preg_match('/\-\-[0-9a-z]{28}/s', $body, $output);
        if ( is_array($output) && count($output)==1 ) {
            $keyword = $output[0];
            $index = strpos($body, $keyword);
            $body = substr($body, 0, $index);
        }
        return $body;
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
