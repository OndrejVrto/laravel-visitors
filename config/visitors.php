<?php

/**
 * for packages: ondrej-vrto/laravel-visitors
 * @see https://github.com/OndrejVrto/laravel-visitors
 */

return [

    /**
    * --------------------------------------------------------------------------
    * Eloquent settings
    * --------------------------------------------------------------------------
    *
    * Here you can configure connection for store data in database.
    */

    'eloquent_connection' => env('DB_CONNECTION', 'mysql'),

    /**
    * Here you can configure the table names in database.
    */

    'table_names' => [
        'expires'     => 'visitors_expires',
        'data'        => 'visitors_data',
        'statistics'  => 'visitors_statistics',
        'daily_graph' => 'visitors_daily_graph',
    ],


    /**
    * --------------------------------------------------------------------------
    * Categories
    * --------------------------------------------------------------------------
    *
    * Use one of the options of the enum VisitCategory to set
    * the default category.
    *
    * Default: OndrejVrto\Visitors\Enums\VisitorCategory::UNDEFINED
    */

    'default_category' => OndrejVrto\Visitors\Enums\VisitorCategory::UNDEFINED,


    /**
    * --------------------------------------------------------------------------
    * Default expires time
    * --------------------------------------------------------------------------
    *
    * If you want set expiration time for ip adress and models in minutes.
    * Ignore this setting apply forceIncrement() method
    *
    * Default: 15
    */

    'expires_time' => 15,  // in minutes


    /**
    * --------------------------------------------------------------------------
    * Ignore Bots and IP addresses
    * --------------------------------------------------------------------------
    *
    * If you want to ignore bots, you can specify that here. The default
    * service that determines if a visitor is a crawler is a package
    * by JayBizzle called CrawlerDetect.
    *
    * Default value: false
    */

    'storage_request_from_crawlers_and_bots' => false,

    /**
    * Ignore views of the following IP addresses.
    */

    'ignored_ip_addresses' => [
        // '127.0.0.1',
    ],


    /**
    * --------------------------------------------------------------------------
    * Statistics and Graph data
    * --------------------------------------------------------------------------
    *
    * The number of days for which traffic statistics are created from today.
    * Warning: Older data will be permanently deleted.
    *
    * Value range  : 1 day - 36500 days
    * Default value: 730 (two years)
    */

    'number_days_statistics' => 365,

    /**
    * Create separate daily graphs for used categories.
    *
    * Warning: Slows down daily graph data generation.
    * Default: false
    */

    'create_categories_statistics' => false,

    /**
    * Create separate daily graphs for crawler.
    *
    * Note   : If is set "storage_request_from_crawlers_and_bots" to true or apply withCrawlers() method.
    * Warning: Slows down daily graph data generation.
    * Default: false
    */
    'create_crawlers_statistics' => false,
];
