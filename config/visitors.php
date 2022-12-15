<?php

/**
 * for packages: ondrej-vrto/laravel-visitors
 * @see https://github.com/artesaos/seotools
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
    *
    * Defining different categories for the records
    * TODO this texts
    *
    */

    'categories_list' => OndrejVrto\Visitors\Enums\Category::class,

    'default_category' => null,
    // 'default_category' => OndrejVrto\Visitors\Enums\Category::WEB,


    /**
    * --------------------------------------------------------------------------
    * Default expires time
    * --------------------------------------------------------------------------
    *
    * If you want set expiration time for ip adress and models in minutes.
    * Ignore this setting apply forceIncrement() method
    *
    * I recommend leaving this enabled
    */

    'with_remember_expiration_for_all_ip' => true,

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
