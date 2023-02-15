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

### Publish

```bash
php artisan vendor:publish --provider="OndrejVrto\Visitors\VisitorsServiceProvider"
# or separately
php artisan vendor:publish --tag=visitors-config
php artisan vendor:publish --tag=visitors-migrations
php artisan vendor:publish --tag=visitors-translations
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

Some in frontend controler add visit counter.

```php
public function show()
{
    $post = Post::find(5);
    $post->incrementVisit();

    $commercial = Commercial::find(100);
    $commercial->incrementVisit();

    return view('post.show', compact('post', 'commercial'));
}
```

## Basic usage display statistics

In dashboard 

```php
public function dashboard()
{
    $topTenYearVisitsByPerson = Traffic::list()
        ->visitedByPersons()
        ->orderByLast365Days()
        ->withRelationship()
        ->limit(10)
        ->get();
    
    $sumarAllVisitFromWeb = Traffic::summary()
        ->visitedByPersons()
        ->inCategory(VisitorCategory::WEB)
        ->first();

    $sumarAllVisitFromApi = Traffic::summary()
        ->visitedByCrawlersAndPersons()
        ->inCategory(VisitorCategory::API)
        ->first();

    return view(
        'dashboard',
        compact('topTenYearVisitsByPerson', 'sumarAllVisit', '$sumarAllVisitFromApi')
    );
}
```

Or list of posts in admin controller.

```php
public function index()
{
    $posts = Post::withTraffic()->paginate(10);

    return view('post.index', compact('posts'));
}
```

Or in detail post in admin controller.

```php
public function show(Post $post)
{
    $visits = Traffic::single($post)
        ->inCategory(VisitorCategory::WEB)
        ->visitedByPersons()
        ->first();

    return view('post.show', compact('post', 'visits'));
}
```

## Customization visit counter

Increment methods

```php
// methods from model
$post->incrementVisit();
$post->incrementVisitForce();
// or with Facade
Visit::model($post)->increment();
// or with helper function
visit($post)->increment();
```

Options

```php
// Check expiration time for ip address, model and category is is set
visit($post)->increment();
// Expiration time off
visit($post)->forceIncrement();

// With defining different categories for the record from Backed Enums
visit($post)->inCategory(VisitorCategory::WEB)->increment();

// Crawlers detection enabled/disabled. Rewrite config settings
visit($post)->withCrawlers()->increment();
visit($post)->withoutCrawlers()->increment();

// Rewrite default expires time
$expiresAt = now()->addHours(3); // `DateTimeInterface` instance
$expiresAt = 60; // minutes in Integer
visit($post)->expiresAt($expiresAt)->increment();

// dynamicaly create ip ignore list
visit($post)->addIpAddressToIgnoreList(['127.0.0.1', '147.7.54.789'])->increment();
```

Manually added data.

```php
visit($post)
    ->fromIP('127.0.0.1')
    ->isCrawler()
    ->isPerson()
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

## Generate traffic data

***Note:*** Queue service is required

```bash
php artisan visitors:fresh
```

```php
// Manual in controller
Artisan::call("visitors:fresh");
// OR
(new TrafficGenerator())->run();
// Automatic in Scheduler (in App\Console\Kernel)
$schedule->command(VisitorsFreshCommand::class)->everyThreeHours();
```

Scheduler is implement in package.
If is set `schedule_generate_traffic_data_automaticaly` to `true` nothing else needs to be set up.


## View traffic data
With preety graph in SVG, count of language and operating system statistics.
### Aggregated data from several models

***Note:*** Return only one record

```php
// summary global
$sumary = Traffic::summary()->first();    // with Facade
$sumary = traffic()->summary()->first();  // with helper function

// summary for all type models and all categories
$sumary = Traffic::summary()->visitedByPersons()->first();
$sumary = Traffic::summary()->visitedByCrawlers()->first();
$sumary = Traffic::summary()->visitedByCrawlersAndPersons()->first();

// summary for all type models and one category
$sumary = Traffic::summary()->inCategory(VisitorCategory::WEB)->first();

// summary for one type model and all categories
$sumary = Traffic::summary()->forModel(Post::class)->first();
$sumary = Traffic::summary()->forModel('App\Models\Post')->first();

// speciffic select
$sumary = Traffic::summary()
    ->forModel(Post::class)
    ->inCategory(VisitorCategory::WEB)
    ->visitedByCrawlersAndPersons()
    ->first();
```

### Trafic for a one specific model

***Note:*** Return only one record

```php
$post = Post::find(1);

// Return model instance Traffic or null
$single = Traffic::single($post)->first();    // with Facade
$single = traffic()->single($post)->first();  // with helper function

// adds relationships to the Visitable Model
$single = Traffic::single($post)->withRelationship()->first();
```

Other options similar to statistics in the previous chapter

```php
// summary for one model for all categories visited by persons
$single = Traffic::single($post)->visitedByPersons()->first();

// summary for one model for one category and all bots
$single = Traffic::single($post)->inCategory(VisitorCategory::WEB)->first();

// summary for one model for one category visited only persons
$single = Traffic::single($post)
    ->inCategory(VisitorCategory::WEB)
    ->visitedByCrawlersAndPersons()
    ->first();
```

### Lists of top visit models

***Note:*** Return Collection of model instances Traffic

```php
// Return Eloquent Builder
$traffic = Traffic::list();    // with Facade
$traffic = traffic()->list();  // with helper function

// Return collection, first model from collection or paginator
$traffic = Traffic::list()->get();
$traffic = Traffic::list()->first();
$traffic = Traffic::list()->paginate();

// adds relationships to the Visitable Model
$traffic = traffic()->list()->withRelationship()->get();

// only specific models type
$models = [VisitableModel::class, AnotherVisitableModel::class, "App\Models\Post"];
$traffic = Traffic::list($models)->get();
```

Other options

```php
$traffic = Traffic::list($models)
    ->inCategory(VisitorCategory::WEB)
    ->inCategories([VisitorCategory::WEB, VisitorCategory::API])
    ->forModel(Post::class)
    ->addModels([Example::class])
    ->orderByTotal()
    ->orderByLastDay()
    ->orderByLast7Days()
    ->orderByLast30Days()
    ->orderByLast365Days()
    ->orderBy('column_name', 'asc')
    ->visitedByPersons()
    ->visitedByCrawlers()
    ->visitedByCrawlersAndPersons()
    ->withRelationship()
    ->limit(10)
    ->get();   //->first();   //->paginate();
```

## Relationship scope for model is posible

Join all traffic data to Visitable model

```php
$post = Post::find($id)->withTraffic()->first();
// all scopes
$posts = Post::query()
    ->withTraffic()
    ->orderByTotal()
    ->orderByLastDay()
    ->orderByLast7Days()
    ->orderByLast30Days()
    ->orderByLast365Days()
    ->paginate();
```

## Get the status and additional data about the latest traffic generation process

Displays the parameters of the last run of the generator and the difference from the current data

```php
$info = Traffic::info();
```

## Language
Enum classes 'OperatingSystem' and 'VisitorCategory' are translatable.

## Configuration package
```php
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

<!-- * [brendt/php-sparkline](https://github.com/brendt/php-sparkline) -->

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
