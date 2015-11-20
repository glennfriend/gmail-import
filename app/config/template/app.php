<?php

/**
 *  app config
 *  example:
 *      echo Config::get('app.env');
 *
 */
return [

    /**
     *  Environment
     *
     *      training    - 開發者環境
     *      production  - 正式環境
     */
    'env' => 'training',

    /**
     *  app path
     */
    'path' => '/var/www/gmail-import',

    /**
     *  Project name
     */
    'name' => 'Inbox Storage',

    /**
     *  timezone
     *
     *      +0 => UTC
     *      -7 => America/Los_Angeles
     *      +8 => Asia/Taipei
     */
    'timezone' => 'America/Los_Angeles',

];
