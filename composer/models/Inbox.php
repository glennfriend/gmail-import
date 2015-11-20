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
            'fromEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getFromEmail',
                'field'   => 'from_email',
            ],
            'toEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getToEmail',
                'field'   => 'to_email',
            ],
            'replyToEmail' => [
                'type'    => 'string',
                'filters' => ['strip_tags','trim'],
                'storage' => 'getReplyToEmail',
                'field'   => 'reply_to_email',
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

    // trim
    protected function filter_message_trim( $value )
    {
        return trim($value);
    }

    /* ------------------------------------------------------------------------------------------------------------------------
        lazy loading methods
    ------------------------------------------------------------------------------------------------------------------------ */
}
