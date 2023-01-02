```php

// -----------------------------------------------------------------------------
// APPLY TRAIT TO MODELS
// -----------------------------------------------------------------------------
class Post extends Model implements Visitable
{
    use InteractsWithVisits;

	// Remove visits on delete model
	protected $removeDataOnDelete = true;

    // ...
}

// -----------------------------------------------------------------------------
// STORE VISIT DATA
// -----------------------------------------------------------------------------

// Add new visit
public function show(Post $post)
{
    visit($post)->increment();

    return view('post.show', compact('post'));
}

// Basic
$post = Post::find(1);

// Store expiration time for all ip address
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
	->fromIP('127.0.0.1');
	->isCrawler(true);
	->fromCountry('sk');
	->inLanguage('sk_SK');
	->fromBrowserAgent('custom browser agent string ....');
	->fromOperatingSystem(OperatingSystem::WINDOWS);
	->visitedAt(Carbon::now()->addMinute(5))
	->increment();


// -----------------------------------------------------------------------------
// PRUNE MODELS
// Note:  Pruning run automaticaly before start generator statistics and trafic
// -----------------------------------------------------------------------------

// in App\Console\Kernel
$schedule->command('model:prune')->daily();
// OR
$schedule->command('model:prune', [
    '--model' => [VisitorsData::class, VisitorsExpires::class],
])->daily();
// OR
Artisan::call("visitors:clean");


// -----------------------------------------------------------------------------
// GENERATE STATISTICS AND TRAFFIC RECORDS FROM VISITOR DATA
// Note: Queue service is required
// -----------------------------------------------------------------------------

// Manual in controller
Artisan::call("visitors:fresh");
// OR
(new StatisticsGenerator())->run();
// Automatic in Scheduler (in App\Console\Kernel)
$schedule->command(VisitorsFreshCommand::class)->dailyAt('01:00');


// -----------------------------------------------------------------------------
// GLOBAL STATISTICS SUMMARY WITH COUNT OF LANGUAGE, OPERATING SYSTEM AND COUNTRY.
// Return only one record
// -----------------------------------------------------------------------------

$statistics = traffic_statistics()->sumar();
$statistics = traffic_statistics()->visitedByPersons()->sumar();
$statistics = traffic_statistics()->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// TOTAL TRAFFIC SUMMARY
// Return only one record
// -----------------------------------------------------------------------------

// summary for all type models and all categories
$trafic = traffic()->sumar();                      // similar to traffic_statistics()->sumar();
$trafic = traffic()->visitedByPersons()->sumar();  // similar to traffic_statistics()->persons();
$trafic = traffic()->visitedByCrawlers()->sumar(); // similar to traffic_statistics()->crawlers();

// summary for all type models and one category
$trafic = traffic()->inCategory(VisitorCategory::WEB)->sumar();
$trafic = traffic()->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
$trafic = traffic()->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();

// summary for one type model and all categories
$trafic = traffic()->forModels(Post::class)->sumar();
$trafic = traffic()->forModels('App\Models\Post')->sumar();
$trafic = traffic()->forModels(Post::class)->visitedByPersons()->sumar();
$trafic = traffic()->forModels(Post::class)->visitedByCrawlers()->sumar();

// summary for one type model and one category
$trafic = traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->sumar();
$trafic = traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
$trafic = traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// SUMMARY FOR A SPECIFIC TYPE MODEL
// Return only one record
// -----------------------------------------------------------------------------

$post = Post::find(1);

$trafic = traffic()->for($post)->inCategory(VisitorCategory::WEB)->sumar();
$trafic = traffic()->for($post)->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
$trafic = traffic()->for($post)->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// LISTS OF TOP VISIT MODELS
// Return Collection of models
// -----------------------------------------------------------------------------

// Define type of model
$list = trafficList(Post::class)->get();
$list = trafficList('App\Models\Post')->get();
$list = trafficList(new Post())->get();
$post = Post::find(1);
$list = trafficList($post)->get();
// define list of type models
$list = trafficList([Post::class, Article::class, Album::class])->get();

// order by
$list = trafficList(Post::class)->orderByTotal()->get();
$list = trafficList(Post::class)->orderByLastDay()->get();
$list = trafficList(Post::class)->orderByLast7Days()->get();
$list = trafficList(Post::class)->orderByLast30Days()->get();
$list = trafficList(Post::class)->orderByLast365Days()->get();
// order direction
$list = trafficList(Post::class)->orderByTotal('asc')->get();
$list = trafficList(Post::class)->orderByTotal('desc')->get();

// visited by persons or crawlers
$list = trafficList(Post::class)->visitedByPersons()->get();
$list = trafficList(Post::class)->visitedByCrawlers()->get();

// visited in categories
$list = trafficList(Post::class)->inCategory(VisitorCategory::WEB)->get();
$list = trafficList(Post::class)->inCategory([VisitorCategory::WEB, VisitorCategory::API])->get();

// adds relationships to the Visitable Model
$list = trafficList(Post::class)->withRelationships()->get();

// apply limit or paginator or another eloquent query builder methods
$list = trafficList(Post::class)->limit(50)->paginate(10);

// Typical Example
$list = trafficList([Post::class, Article::class])
	->inCategory(VisitorCategory::WEB)
	->visitedByPersons()
	->withRelationships()
	->topLast30Days()
	->limit(50)
	->paginate(10);


// -----------------------------------------------------------------------------
// ADD RELATIONSHIPS TO MODEL
// -----------------------------------------------------------------------------

// Get all statistic data from eager loading
Post::query()->with('visitTraffic')->get();
Post::find($id)->with('visitTraffic')->get();
Post::with('visitTraffic')->limit(50)->paginate(10);
