<?php

// config for ondrej-vrto/laravel-visitors
return [

    /**
    * --------------------------------------------------------------------------
    * Eloquent Models
    * --------------------------------------------------------------------------
    */
    'models' => [
        /**
        * Here you can configure connection to database.
        */
        'connection' => env('DB_CONNECTION', 'mysql'),

        /**
         * Here you can configure the table names.
         */
        'table_names' => [
            'expires'    => 'visitors_expires',
            'data'       => 'visitors_data',
            'statistics' => 'visitors_statistics',
        ],

        /**
         * Here you can configure the name of coloumns for model morphs.
         */
        'model_morph_key' => 'viewable',
    ],

    /**
    * --------------------------------------------------------------------------
    * Tags
    * --------------------------------------------------------------------------
    */
    'tags' => [
        'default_tag' => 'web',

        /**
        *  Remember x seconds of time for all tags for all ip address
        *  Will count only one visit of an IP during this specified time.
        */
        'tags_remember' => [
            'web'   => 15 * 60, // 15 minutes
            'api'   =>  5 * 60, // 5 minutes
            'admin' => 60 * 60, // 60 minutes
        ],
    ],

    /**
    * --------------------------------------------------------------------------
    * Ignore Bots
    * --------------------------------------------------------------------------
    *
    * If you want to ignore bots, you can specify that here. The default
    * service that determines if a visitor is a crawler is a package
    * by JayBizzle called CrawlerDetect.
    *
    */
    'ignore_bots' => true,

    /**
    * --------------------------------------------------------------------------
    * Ignore IP Addresses
    * --------------------------------------------------------------------------
    *
    * Ignore views of the following IP addresses.
    *
    */

    'ignored_ip_addresses' => [
        // '127.0.0.1',
    ],
];
