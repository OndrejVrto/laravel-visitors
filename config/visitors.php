<?php

/**
 * for packages: ondrej-vrto/laravel-visitors
 * @see https://github.com/OndrejVrto/laravel-visitors
 */

return [

    /**
    * --------------------------------------------------------------------------
    * Eloquent Models
    * --------------------------------------------------------------------------
    *
    * TODO this texts
    *
    */

    'models' => [
        /**
        * Here you can configure connection to database.
        */
        'eloquent_connection' => env('DB_CONNECTION', 'mysql'),

        /**
         * Here you can configure the table names.
         */
        'table_names' => [
            'expires'    => 'visitors_expires',
            'data'       => 'visitors_data',
            'statistics' => 'visitors_statistics',
        ],
    ],


    /**
    * --------------------------------------------------------------------------
    * Categories
    * --------------------------------------------------------------------------
    */

    // 'default_category' => null,
    'default_category' => OndrejVrto\Visitors\Enums\VisitorCategory::UNDEFINED,


    /**
    * --------------------------------------------------------------------------
    * Default expires time
    * --------------------------------------------------------------------------
    *
    * If you want set expiration time for ip adress and models in minutes.
    * Ignore this setting apply forceIncrement() method
    *
    */

    'expires_time' => 15,  // in minutes


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

    'storage_request_from_crawlers_and_bots' => false,


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
