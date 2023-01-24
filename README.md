<p align="center"><img src="./.github/img/socialcard.png" alt="Social Card of PHP Line Chart"></p>

# A Laravel package that allows you to track Eloquent model traffic.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ondrej-vrto/laravel-visitors.svg?style=flat-square)](https://packagist.org/packages/ondrej-vrto/laravel-visitors)
[![Tests](https://img.shields.io/github/actions/workflow/status/OndrejVrto/laravel-visitors/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/OndrejVrto/laravel-visitors/blob/main/.github/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/ondrej-vrto/laravel-visitors.svg?style=flat-square)](https://packagist.org/packages/ondrej-vrto/laravel-visitors)

## Installation

You can install the package via composer:

```bash
composer require ondrej-vrto/laravel-visitors
```

## Basic usage visit counter

Apply contract "Visitable" and trait "InteractsWithVisits" to model

```php
class Post extends Model implements Visitable
{
    use InteractsWithVisits;

    // Remove visits on delete model
    protected $removeDataOnDelete = true;

    // ...
}
```
And add visit counter to controler.
```php
public function show(Post $post)
{
    visit($post)->increment();

    return view('post.show', compact('post'));
}
```

## Customization visit counter

```php
$post = Post::find(1);

// Check expiration time for request for all ip address and model
visit($post)->increment();
// Expiration time off
visit($post)->forceIncrement();

// With defining different categories for the record from Backed Enums
visit($post)->inCategory(VisitorCategory::WEB)->increment();
visit($post)->inCategory(VisitorCategory::API)->increment();
visit($post)->inCategory(VisitorCategory::AUTHENTICATED)->increment();
visit($post)->inCategory(VisitorCategory::GUEST)->increment();

// Crawlers detection enabled/disabled. Rewrite config settings
visit($post)->withCrawlers()->increment();
visit($post)->withoutCrawlers()->increment();

// Rewrite default expires time
$expiresAt = now()->addHours(3); // `DateTimeInterface` instance
visit($post)->expiresAt($expiresAt)->increment();
// OR
$minutes = 60; // Integer
visit($post)->expiresAt($minutes)->increment();

// dynamicaly create ip ignore list
visit($post)->addIpAddressToIgnoreList(['127.0.0.1', '147.7.54.789'])->increment();

// Manually added data. Rewrite default values
visit($post)
    ->fromIP('127.0.0.1')
    ->isCrawler()
    ->isPerson()
    ->fromCountry('sk')
    ->inLanguage('Esperanto')
    ->fromBrowserAgent('custom browser agent string ....')
    ->fromOperatingSystem(OperatingSystem::WINDOWS)
    ->visitedAt(Carbon::now()->addMinute(5))
    ->increment();
```

## Pruning models

***Note:***  Pruning run automaticaly before start generator statistics and trafic

```bash
php artisan visitors:clean
```

```php
// in App\Console\Kernel
$schedule->command('model:prune')->daily();
// OR
$schedule->command('model:prune', [
    '--model' => [VisitorsData::class, VisitorsExpires::class],
])->daily();
// OR
Artisan::call("visitors:clean");
```

## Generate statistics and traffic records from visitor data

***Note:*** Queue service is required

```bash
php artisan visitors:fresh
```

```php
// Manual in controller
Artisan::call("visitors:fresh");
// OR
(new StatisticsGenerator())->run();
// Automatic in Scheduler (in App\Console\Kernel)
$schedule->command(VisitorsFreshCommand::class)->dailyAt('01:00');
```

Scheduler is implement in package. If is set `schedule_generate_traffic_data_automaticaly` to `true` nothing else needs to be set up.

## Statistics summary

With preety graph in SVG, count of language, operating system and country statistics.

### Usage
***Note:*** Return only one record

```php
// summary global
$sumary = Statistics::sumar();    // with Facade
$sumary = statistics()->sumar();  // with helper function

// summary for all type models and all categories
$sumary = statistics()->visitedByPersons()->sumar();
$sumary = statistics()->visitedByCrawlers()->sumar();
$sumary = statistics()->visitedByCrawlersAndPersons()->sumar();

// summary for all type models and one category
$sumary = statistics()->inCategory(VisitorCategory::WEB)->sumar();
$sumary = statistics()->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
$sumary = statistics()->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();
$sumary = statistics()->inCategory(VisitorCategory::WEB)->visitedByCrawlersAndPersons()->sumar();

// summary for one type model and all categories
$sumary = statistics()->forModel(Post::class)->sumar();
$sumary = statistics()->forModel('App\Models\Post')->sumar();
$sumary = statistics()->forModel(Post::class)->visitedByPersons()->sumar();
$sumary = statistics()->forModel(Post::class)->visitedByCrawlers()->sumar();
$sumary = statistics()->forModel(Post::class)->visitedByCrawlersAndPersons()->sumar();

// summary for one type model and one category
$sumary = statistics()->forModel(Post::class)->inCategory(VisitorCategory::WEB)->sumar();
$sumary = statistics()->forModel(Post::class)->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
$sumary = statistics()->forModel(Post::class)->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();
$sumary = statistics()->forModel(Post::class)->inCategory(VisitorCategory::WEB)->visitedByCrawlersAndPersons()->sumar();
```

## Trafic for a one specific type model

### Usage

***Note:*** Return only one record

```php
$post = Post::find(1);

// Return model instance Traffic or null
$traffic = Traffic::forModel($post)->get();    // with Facade
$traffic = traffic()->forModel($post)->get();  // with helper function

// adds relationships to the Visitable Model
$traffic = traffic()->forModel($post)->withRelationship()->get();
```

Other options similar to statistics in the previous chapter

```php
// summary for one model for all categories visited by persons
$traffic = traffic()->forModel($post)->visitedByPersons()->get();

// summary for one model for one category and all bots
$traffic = traffic()->forModel($post)->inCategory(VisitorCategory::WEB)->get();

// summary for one model for one category visited only persons
$traffic = traffic()->forModel($post)->inCategory(VisitorCategory::WEB)->visitedByPersons()->get();
```

## Lists of top visit models

### Usage

***Note:*** Return Collection of model instances Traffic

```php
$models = [VisitableModel::class, AnotherVisitableModel::class, "App\Models\Post"];

// Return Eloquent Builder
$traffic = Traffic::forSeveralModels($models);    // with Facade
$traffic = traffic()->forSeveralModels($models);  // with helper function

// Return collection, first model from collection or paginator
$traffic = Traffic::forSeveralModels($models)->get();
$traffic = Traffic::forSeveralModels($models)->first();
$traffic = Traffic::forSeveralModels($models)->paginate();

// adds relationships to the Visitable Model
$traffic = traffic()->forSeveralModels($models)->withRelationship()->get();
```

Other options

```php
$traffic = Traffic::forSeveralModels($models)
    ->inCategories([VisitorCategory::WEB, VisitorCategory::API])
    ->addModels([Example::class])
    ->orderByTotal()
    ->orderByLastDay()
    ->orderByLast7Days()
    ->orderByLast30Days()
    ->orderByLast365Days()
    ->orderBy('column_name', 'asc')
    ->visitedByPersons()
    ->visitedByCrawlers();
    ->visitedByCrawlersAndPersons()
    ->withRelationship()
    ->limit(10)
    ->get()
```

## Relationship for model is posible

```php
// Get all statistic data from eager loading
Post::query()->with('visitTraffic')->get();
Post::find($id)->with('visitTraffic')->get();
Post::with('visitTraffic')->limit(50)->paginate(10);
```

## Configuration package
```php
    /**
     * --------------------------------------------------------------------------
     * Eloquent settings
     * --------------------------------------------------------------------------
     *
     * Here you can configure connection for store data in database.
     */

    'eloquent_connection' => env('VISITORS_DB_CONNECTION', 'mysql'),


    /**
     * Here you can configure the table names in database.
     */

    'table_names' => [
        'data'        => 'visitors_data',
        'expires'     => 'visitors_expires',
        'traffic'     => 'visitors_traffic',
        'statistics'  => 'visitors_statistics',
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

    'expires_time_for_visit' => 15,  // in minutes


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
     * Statistics and traffic data
     * --------------------------------------------------------------------------
     *
     * The number of days after which traffic data will be deleted from today.
     * Warning: Older data will be permanently deleted.
     *
     * Value range  : 1 day - 36500 days
     * Default value: 730 (two years)
     */

    'number_days_traffic' => 730,


    /**
     * Create separate daily traffic graphs for used categories.
     *
     * Warning: Slows down data generation.
     * Default: false
     */

    'generate_traffic_for_categories' => false,


    /**
     * Create separate daily traffic graphs for crawlers and persons.
     *
     * Note   : If is set "storage_request_from_crawlers_and_bots" to true 
     *          or apply withCrawlers() method.
     * Warning: Slows down data generation.
     * Default: false
     */

    'generate_traffic_for_crawlers_and_persons' => false,


    /**
     * Schedule the generation of traffic data and statistics within the internal
     * scheduler of this package. It will run every three hours.
     *
     * Note   : Equivalent to setting in the scheduler (in App\Console\Kernel)
     *          $schedule->command(VisitorsFreshCommand::class)->everyThreeHours();
     * Default: true
     */

    'schedule_generate_traffic_data_automaticaly' => true,


    /**
     * --------------------------------------------------------------------------
     * Line graphs in SVG
     * --------------------------------------------------------------------------
     *
     * Note:  https://github.com/OndrejVrto/php-linechart
     */

    'generate_graphs' => true,

    'custom_graphs_appearance' => [

        'traffic' => [
            'maximum_value_lock' => null,
            'maximum_days'       => null,
            'order_reverse'      => false,
            'width_svg'          => 1000,
            'height_svg'         => 50,
            'stroke_width'       => 2,
            'colors'             => ['#4285F4', '#31ACF2', '#2BC9F4'],
        ],

        'statistics' => [
            'maximum_value_lock' => null,
            'maximum_days'       => null,
            'order_reverse'      => false,
            'width_svg'          => 1000,
            'height_svg'         => 50,
            'stroke_width'       => 2,
            'colors'             => ['#c82161', '#fe2977', '#b848f5', '#b848f5'],
        ],
    ],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

<!-- ## Security Vulnerabilities

Please review [our security policy](../../security/policy)on how to report security vulnerabilities.-->

## Credits

- [Ondrej Vrťo](https://github.com/OndrejVrto)
- [All Contributors](../../contributors)

## Alternatives

* [brendt/php-sparkline](https://github.com/brendt/php-sparkline)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
