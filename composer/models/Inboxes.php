<?php

/**
 *
 */
class Inboxes extends ZendModel
{
    const CACHE_INBOX = 'cache_inbox';

    /**
     *  table name
     */
    protected $tableName = 'inboxes';

    /**
     *  get method
     */
    protected $getMethod = 'getInbox';

    /**
     *  get db object by record
     *  @param  row
     *  @return TahScan object
     */
    public function mapRow( $row )
    {
        $object = new Inbox();
        $object->setId              ( $row['id']                            );
        $object->setMessageId       ( $row['message_id']                    );
        $object->setFromEmail       ( $row['from_email']                    );
        $object->setReplyToEmail    ( $row['reply_to_email']                );
        $object->setToEmail         ( $row['to_email']                      );
        $object->setFromName        ( $row['from_name']                     );
        $object->setReplyToName     ( $row['reply_to_name']                 );
        $object->setToName          ( $row['to_name']                       );
        $object->setSubject         ( $row['subject']                       );
        $object->setContent         ( $row['content']                       );
        $object->setEmailCreateTime ( strtotime($row['email_create_time'])  );
        $object->setProperties      ( unserialize($row['properties'])       );
        return $object;
    }

    /* ================================================================================
        write database
    ================================================================================ */

    /**
     *  add Inbox
     *  @param Inbox object
     *  @return insert id or false
     */
    public function addInbox($object)
    {
        $insertId = $this->addObject($object, true);
        if (!$insertId) {
            return false;
        }

        $object = $this->getInbox($insertId);
        if (!$object) {
            return false;
        }

        $this->preChangeHook($object);
        return $insertId;
    }

    /**
     *  pre change hook, first remove cache, second do something more
     *  about add, update, delete
     *  @param object
     */
    public function preChangeHook($object)
    {
        // first, remove cache
        $this->removeCache($object);
    }

    /**
     *  remove cache
     *  @param object
     */
    protected function removeCache($object)
    {
        if ( $object->getId() <= 0 ) {
            return;
        }

        $cacheKey = $this->getFullCacheKey( $object->getId(), Inboxes::CACHE_INBOX );
        Bridge\Cache::remove( $cacheKey );
    }

    /* ================================================================================
        read access database
    ================================================================================ */

    /**
     *  get Inbox by id
     *  @param  int id
     *  @return object or false
     */
    public function getInbox( $id )
    {
        $object = $this->getObject( 'id', $id, Inboxes::CACHE_INBOX );
        if ( !$object ) {
            return false;
        }
        return $object;
    }

    /* ================================================================================
        find Inboxes and get count
        多欄、針對性的搜尋, 主要在後台方便使用, 使用 and 搜尋方式
    ================================================================================ */

    /**
     *  find many Inbox
     *  @param  option array
     *  @return objects or empty array
     */
    public function findInboxes($opt=[])
    {
        $opt += [
            '_order'        => 'id,DESC',
            '_page'         => 1,
            '_itemsPerPage' => Lib\Config::get('db.items_per_page')
        ];
        return $this->findInboxesReal( $opt );
    }

    /**
     *  get count by "findInboxes" method
     *  @return int
     */
    public function numFindInboxes($opt=[])
    {
        // $opt += [];
        return $this->findInboxesReal($opt, true);
    }

    /**
     *  findInboxes option
     *  @return objects or record total
     */
    protected function findInboxesReal($opt=[], $isGetCount=false)
    {
        // validate 欄位 白名單
        $list = [
            'fields' => [
                'id'            => 'id',
                'messageId'     => 'message_id',
                'fromEmail'     => 'from_email',
                'replyToEmail'  => 'reply_to_email',
                'toEmail'       => 'to_email',
                'fromName'      => 'from_name',
                'replyToName'   => 'reply_to_name',
                'toName'        => 'to_name',
                'subject'       => 'subject',
                'content'       => 'content',
            ],
            'option' => [
                '_order',
                '_page',
                '_itemsPerPage',
            ]
        ];

        ZendModelWhiteListHelper::validateFields($opt, $list);
        ZendModelWhiteListHelper::filterOrder($opt, $list);
        ZendModelWhiteListHelper::fieldValueNullToEmpty($opt);

        $select = $this->getDbSelect();
        $field = $list['fields'];

        if ( isset($opt['messageId']) ) {
            $select->where->and->equalTo( $field['messageId'], $opt['messageId'] );
        }

        if ( isset($opt['fromEmail']) ) {
            $select->where->and->equalTo( $field['fromEmail'], $opt['fromEmail'] );
        }
        if ( isset($opt['replyToEmail']) ) {
            $select->where->and->equalTo( $field['replyToEmail'], $opt['replyToEmail'] );
        }
        if ( isset($opt['toEmail']) ) {
            $select->where->and->equalTo( $field['toEmail'], $opt['toEmail'] );
        }

        if ( isset($opt['fromName']) ) {
            $select->where->and->equalTo( $field['fromName'], $opt['fromName'] );
        }
        if ( isset($opt['replyToName']) ) {
            $select->where->and->equalTo( $field['replyToName'], $opt['replyToName'] );
        }
        if ( isset($opt['toName']) ) {
            $select->where->and->equalTo( $field['toName'], $opt['toName'] );
        }

        if ( isset($opt['subject']) ) {
            $select->where->and->like( $field['subject'], '%'.$opt['subject'].'%' );
        }
        if ( isset($opt['content']) ) {
            $select->where->and->like( $field['content'], '%'.$opt['content'].'%' );
        }

        if ( !$isGetCount ) {
            return $this->findObjects( $select, $opt );
        }
        return $this->numFindObjects( $select );
    }

}
