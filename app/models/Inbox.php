<?php

/**
 *  Inbox
 *
 */
class Inbox extends BaseObject
{

    /**
     *  請依照 table 正確填寫該 field 內容
     *  @return array()
     */
    public static function getTableDefinition()
    {
        return [
            'id' => [
                'type'    => 'integer',
                'filters' => ['intval'],
                'storage' => 'getId',
                'field'   => 'id',
            ],
            'messageId' => [
                'type'    => 'string',
                'filters' => ['message_trim'],
                'storage' => 'getMessageId',
                'field'   => 'message_id',
            ],
            'replyToMessageId' => array(
                'type'    => 'string',
                'filters' => array('message_trim'),
                'storage' => 'getReplyToMessageId',
                'field'   => 'reply_to_message_id',
            ),
            'referenceMessageIds' => array(
                'type'    => 'string',
                'filters' => array('message_trim'),
                'storage' => 'getReferenceMessageIds',
                'field'   => 'reference_message_ids',
            ),
            'fromEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getFromEmail',
                'field'   => 'from_email',
            ],
            'replyToEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getReplyToEmail',
                'field'   => 'reply_to_email',
            ],
            'toEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getToEmail',
                'field'   => 'to_email',
            ],
            'fromName' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getFromName',
                'field'   => 'from_name',
            ],
            'replyToName' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getReplyToName',
                'field'   => 'reply_to_name',
            ],
            'toName' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getToName',
                'field'   => 'to_name',
            ],
            'subject' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getSubject',
                'field'   => 'subject',
            ],
            'content' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getContent',
                'field'   => 'content',
            ],
            'emailCreateTime' => [
                'type'    => 'timestamp',
                'filters' => ['dateval'],
                'storage' => 'getEmailCreateTime',
                'field'   => 'email_create_time',
                'value'   => strtotime('1970-01-01'),
            ],
            'properties' => [
                'type'    => 'string',
                'filters' => ['arrayval'],
                'storage' => 'getProperties',
                'field'   => 'properties',
            ],
        ];
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        extends
    ------------------------------------------------------------------------------------------------------------------------ */

    public function getAttachments()
    {
        $attachments = $this->getProperty('info')['attachments'];
        if (!$attachments) {
            return [];
        }

        return [
            'file'      => $attachments['file'],
            'filename'  => $attachments['filename'],
            'path'      => $attachments['path'],
        ];

    }

    // trim
    protected function filter_message_trim( $value )
    {
        return trim($value);
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        lazy loading methods
    ------------------------------------------------------------------------------------------------------------------------ */
}
