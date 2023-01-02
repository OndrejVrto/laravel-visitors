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
visit($post)->increment();

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

// Expiration time off
visit($post)->forceIncrement();

// Manually added data
visit($post)
	->isCrawler(false)
	->fromCountry('sk')
	->inLanguage('sk_SK')
	->fromOperatingSystem(OperatingSystem::WINDOWS)
	->visitedAt(Carbon::now()->addMinute(5))
	->forceIncrement();


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
// ADD RELATIONSHIPS TO MODEL
// -----------------------------------------------------------------------------

// Get all statistic data from eager loading
Post::query()->with('visitTraffic')->get();
Post::find($id)->with('visitTraffic')->get();
Post::with('visitTraffic')->limit(50)->paginate(10);



// -----------------------------------------------------------------------------
// GLOBAL STATISTICS WITH LANGUAGE, OPERATING SYSTEM AND COUNTRY STATISTICS.
// Return one record
// -----------------------------------------------------------------------------

traffic_statistics()->sumar();
traffic_statistics()->visitedByPersons()->sumar();
traffic_statistics()->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// TOTAL TRAFFIC SUMMARY
// Return one record
// -----------------------------------------------------------------------------

// summary for all type models and all categories
traffic()->sumar();                      // similar to traffic_statistics()->sumar();
traffic()->visitedByPersons()->sumar();  // similar to traffic_statistics()->persons();
traffic()->visitedByCrawlers()->sumar(); // similar to traffic_statistics()->crawlers();

// summary for all type models and one category
traffic()->inCategory(VisitorCategory::WEB)->sumar();
traffic()->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
traffic()->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();

// summary for one type model and all categories
traffic()->forModels(Post::class)->sumar();
traffic()->forModels('App\Models\Post')->sumar();
traffic()->forModels(Post::class)->visitedByPersons()->sumar();
traffic()->forModels(Post::class)->visitedByCrawlers()->sumar();

// summary for one type model and one category
traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->sumar();
traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
traffic()->forModels(Post::class)->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// SUMMARY FOR A SPECIFIC TYPE MODEL
// Return one record
// -----------------------------------------------------------------------------

$post = Post::find(1);

traffic()->for($post)->inCategory(VisitorCategory::WEB)->sumar();
traffic()->for($post)->inCategory(VisitorCategory::WEB)->visitedByPersons()->sumar();
traffic()->for($post)->inCategory(VisitorCategory::WEB)->visitedByCrawlers()->sumar();


// -----------------------------------------------------------------------------
// LISTS OF TOP VISIT MODELS
// Return collection models
// -----------------------------------------------------------------------------

// Define type of model
trafficList(Post::class)->get();
trafficList('App\Models\Post')->get();
trafficList(new Post())->get();
$post = Post::find(1);
trafficList($post)->get();
// define list of type models
trafficList([Post::class, Article::class, Album::class])->get();

// order by
trafficList(Post::class)->topTotal()->get();
trafficList(Post::class)->topLastDay()->get();
trafficList(Post::class)->topLast7Days()->get();
trafficList(Post::class)->topLast30Days()->get();
trafficList(Post::class)->topLast365Days()->get();

// visited by persons or crawlers
trafficList(Post::class)->visitedByPersons()->get();
trafficList(Post::class)->visitedByCrawlers()->get();

// visited in categories
trafficList(Post::class)->inCategory(VisitorCategory::WEB)->get();
trafficList(Post::class)->inCategory([VisitorCategory::WEB, VisitorCategory::API])->get();

// adds relationships to the Visitable Model
trafficList(Post::class)->withRelationships()->get();

// apply limit or paginator or another eloquent query builder methods
trafficList(Post::class)->limit(50)->paginate(10);

// Typical Example
trafficList([Post::class, Article::class])
	->inCategory(VisitorCategory::WEB)
	->visitedByPersons()
	->withRelationships()
	->topLast30Days()
	->limit(50)
	->paginate(10);
