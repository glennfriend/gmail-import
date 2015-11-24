<?php

namespace Lib;

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
     *  放置 email temp 的目錄
     */
    protected static $temp = null;

    /**
     *
     */
    public static function init(Array $options)
    {
        if (isset($options['attach_path'])) {
            self::$temp = $options['attach_path'];
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
            // pr($headerInfo); exit;

            // build folder id
            if (   isset($headerInfo)
                && isset($headerInfo->from)
                && isset($headerInfo->from[0])
                && isset($headerInfo->from[0]->mailbox)
            ) {
                $folderId = $headerInfo->from[0]->mailbox . '-' . md5($headerInfo->message_id);
            }
            else {
                $folderId = md5($headerInfo->message_id);
            }

            $bodyText = imap_body($inbox, $id, FT_PEEK);
            list($bodyHeader, $body, $mailAttachments) = self::_parseBody($bodyText, $folderId);
            $fileAttachments = self::_parseAttachments($inbox, $id, $folderId);

            $infos[] = [
                'message_id'        => $headerInfo->message_id,
                'subject'           => $headerInfo->subject,
                'from'              => $headerInfo->from,
                'reply_to'          => $headerInfo->reply_to,
                'to'                => $headerInfo->to,
                'date'              => $headerInfo->MailDate,
                'body_header'       => $bodyHeader,
                'body'              => htmlspecialchars($body),
                'mail_attachments'  => $mailAttachments,
                'file_attachments'  => $fileAttachments,
            ];

            // 設定為已讀
            if ($isSettingRead) {
                imap_body($inbox, $id, 0);
            }

        }

        self::$errorMessage = null;
        self::_close();
        return $infos;
    }

    /**
     *  parse 附件
     *  "不" 包含內文中的 mime 檔案
     *
     *  @return array
     */
    private static function _parseAttachments($inbox, $id, $folderId)
    {
        //
        $structure = imap_fetchstructure($inbox, $id);

        if (!isset($structure->parts) || count($structure->parts) <= 0) {
            return [];
        }

        $infos = [];
        for ($i = 0; $i < count($structure->parts); $i++) {

            $infos[$i] = array(
                'is_attachment' => false,
                'filename' => '',
                'name' => '',
                'attachment' => '',
            );

            if ($structure->parts[$i]->ifdparameters) {
                foreach($structure->parts[$i]->dparameters as $object) {
                    if(strtolower($object->attribute) == 'filename') {
                        $infos[$i]['is_attachment'] = true;
                        $infos[$i]['filename'] = $object->value;
                    }
                }
            }

            if ($structure->parts[$i]->ifparameters) {
                foreach($structure->parts[$i]->parameters as $object) {
                    if(strtolower($object->attribute) == 'name') {
                        $infos[$i]['is_attachment'] = true;
                        $infos[$i]['name'] = $object->value;
                    }
                }
            }

            if ($infos[$i]['is_attachment']) {
                $infos[$i]['attachment'] = imap_fetchbody($inbox, $id, $i+1, FT_PEEK);
                // 3 = BASE64
                if ( 3 == $structure->parts[$i]->encoding ) {
                    $infos[$i]['attachment'] = base64_decode($infos[$i]['attachment']);
                }
                // 4 = QUOTED-PRINTABLE
                elseif ( 4 == $structure->parts[$i]->encoding ) {
                    $infos[$i]['attachment'] = quoted_printable_decode($infos[$i]['attachment']);
                }
            }
        }

        $attachmentsInfos = [];
        foreach ($infos as $index => $attachment) {
            if (!$attachment['is_attachment']) {
                continue;
            }

            $name = self::_decodeMailString(trim($attachment['name']));
            if (!$name) {
                $name = "unknown_{$index}";
            }
            $filename = self::_getFilenameByName($name);

            $path = self::$temp . "/var/attach/{$folderId}";
            if (!file_exists($path)) {
                mkdir($path);
            }
            file_put_contents( $path . '/' . $filename, $attachment['attachment']);

            $decodeFailName = 'unknown_' . $index;
            $attachmentsInfos[] = [
                'name'      => $name,
                'filename'  => $filename,
                'path'      => $path . '/' . $filename,
            ];
        }

        return $attachmentsInfos;
    }

    /**
     *  解析一串 email 帶來的編碼
     *  如果開頭不是 "=?" 符號就不處理
     *
     *  example
     *      =?BIG5?B?pmGyebnYpXEucG5n?=
     *      -> BIG5 to UTF-8
     *      -> 地球壽司.png
     *
     *      =?ISO-2022-JP?B?GyRCQHYwYTUhGyhCX3NuYXBzaG90MjAw?= =?ISO-2022-JP?B?NzA5MTcyMzU0MzkuanBn?=
     *      -> 空白間隔, 重新組合
     *      -> ISO-2022-JP to UTF-8
     *      -> 洗衣機_snapshot20070917235439.jpg
     *
     *  @return name string or false
     */
    private static function _decodeMailString($strings)
    {
        if ('=?' !== substr($strings,0,2)) {
            return $strings;
        }

        $text = '';
        $items = explode(' ', $strings);
        foreach ($items as $code) {
            $tmp = explode('?', $code);
            if (!is_array($tmp)) {
               return false;
            }

            if (!isset($tmp[4])) {
               return false;
            }

            if ( '=' != $tmp[0] ||
                 'B' != $tmp[2] ||
                 '=' != $tmp[4] ) {
               return false;
            }

            $type   = $tmp[1];
            $encode = $tmp[3];
            $decode = base64_decode($encode);
            $text .= iconv($type, 'UTF-8', $decode);
        }
        return $text;
    }

    /**
     *  檔案名稱的建立
     *      - 跟原本的檔案有相關
     *      - 去除不安全的字元
     *      - 不能因為去除字元, 使得檔名有機會重覆
     */
    private static function _getFilenameByName($name)
    {
        $extensionName  = pathinfo($name, PATHINFO_EXTENSION);
        $filename       = pathinfo($name, PATHINFO_FILENAME);
        $filename       = preg_replace("/[^a-zA-Z0-9一-龥\-\_\.]/u", "", $filename);
        $filename       = str_replace('.', '-', $filename);
        $filename      .= '-' . md5($name);
        if ($extensionName) {
            $filename .= '.' . $extensionName;
        }
        return strtolower($filename);
    }

    /**
     *  parse body
     *  包含內文中的 mime 檔案
     *
     *  @see https://github.com/php-mime-mail-parser/php-mime-mail-parser
     *  @return information array
     */
    private static function _parseBody($body, $folderId)
    {
        static $parser;
        if (!$parser) {
            $parser = new \PhpMimeMailParser\Parser();
        }

        $parser->setText($body);

        $attachments = [];
        if (self::$temp) {
            $path = self::$temp . "/var/content/{$folderId}/";
            $parser->saveAttachments($path);
            $infos = $parser->getAttachments();
            foreach ($infos as $info) {
                $attachments[] = [
                    'filename'              => $info->getFilename(),
                    'content_type'          => $info->getContentType(),
                    'content_disposition'   => $info->getContentDisposition(),
                    'content_id'            => $info->getContentID(),
                ];
            }

        }

        $headers = $parser->getHeaders();
        $body    = $parser->getMessageBody();
        $body    = self::_minusBodyContent($body);
        return [$headers, $body, $attachments];
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
    private static function _close()
    {
        $inbox = self::_getInboxes();
        imap_close($inbox);
    }
}
