<?php

declare(strict_types=1);

/*
/ config for packages: ondrej-vrto/laravel-visitors
/ @see https://github.com/OndrejVrto/laravel-visitors
*/

return [

    /*
    / --------------------------------------------------------------------------
    / Eloquent settings
    / --------------------------------------------------------------------------
    /*
    / Here you can configure the table names in database.
    */

    'table_names' => [
        'info'    => 'visitors_info',
        'data'    => 'visitors_data',
        'expires' => 'visitors_expires',
        'traffic' => 'visitors_traffic',
    ],


    /*
    / --------------------------------------------------------------------------
    / Categories
    / --------------------------------------------------------------------------
    /
    / Use one of the options of the enum VisitCategory to set
    / the default category.
    /
    / Default: OndrejVrto\Visitors\Enums\VisitorCategory::UNDEFINED
    */

    'default_category' => OndrejVrto\Visitors\Enums\VisitorCategory::UNDEFINED,


    /*
    / --------------------------------------------------------------------------
    / Default expires time in minutes
    / --------------------------------------------------------------------------
    /
    / If you want set expiration time for ip adress and models in minutes.
    / Ignore this setting apply forceIncrement() method
    /
    / Default: 15
    */

    'expires_time_for_visit' => 15,


    /*
    / --------------------------------------------------------------------------
    / Ignore Bots and IP addresses
    / --------------------------------------------------------------------------
    /
    / If you want to ignore bots, you can specify that here. The default
    / service that determines if a visitor is a crawler is a package
    / by JayBizzle called CrawlerDetect.
    /
    / Default value: false
    */

    'storage_request_from_crawlers_and_bots' => false,


    /*
    / Ignore views of the following IP addresses.
    */

    'ignored_ip_addresses' => [
        // '127.0.0.1',
    ],


    /*
    / --------------------------------------------------------------------------
    / Statistics and traffic data
    / --------------------------------------------------------------------------
    /
    / The number of days after which traffic data will be deleted from today.
    / Warning: Older data will be permanently deleted.
    /
    / Value range  : 1 day - 36500 days
    / Default value: 730 (two years)
    */

    'number_days_traffic' => 730,


    /*
    / Create separate daily traffic graphs for used categories.
    /
    / Warning: Slows down data generation.
    / Default: false
    */

    'generate_traffic_for_categories' => false,


    /*
    / Create separate daily traffic graphs for crawlers and persons.
    /
    / Note   : If is set "storage_request_from_crawlers_and_bots" to true or apply withCrawlers() method.
    / Warning: Slows down data generation.
    / Default: false
    */

    'generate_traffic_for_crawlers_and_persons' => false,


    /*
    / Schedule the generation of traffic data and statistics within
    / the internal scheduler of this package. It will run every three hours.
    /
    / Note   : Equivalent to setting in the scheduler (in App\Console\Kernel)
    /          $schedule->command(VisitorsFreshCommand::class)->everyThreeHours();
    / Default: true
    */

    'schedule_generate_traffic_data_automaticaly' => true,


    /*
    / --------------------------------------------------------------------------
    / Line graphs in SVG
    / --------------------------------------------------------------------------
    /
    / Note:  https://github.com/OndrejVrto/php-linechart
    */

    'generate_graphs' => true,

    'graphs_properties' => [

        'maximum_value_lock' => null,
        'maximum_days'       => null,
        'order_reverse'      => false,
        'width_svg'          => 1000,
        'height_svg'         => 50,
        'stroke_width'       => 2,
        'colors'             => ['#4285F4', '#31ACF2', '#2BC9F4'],

    ],
];
