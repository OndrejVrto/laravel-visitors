```php
// -----------------------------------------------------------------------------
// STORE DATA FOR MODEL
// -----------------------------------------------------------------------------

// Add new visit
public function show(Post $post)
{
    visit($post)->increment();

    return view('post.show', compact('post'));
}

// Basic
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
// GENERATE STATISTICS
// -----------------------------------------------------------------------------

// first fresh statistics
Artisan::call("visitors:fresh");

// update statistics since last run
Artisan::call("visitors:update");

// prune old data
Artisan::call("visitors:clean");


// -----------------------------------------------------------------------------
// GET RESULTING STATISTICS FOR MODELS
// -----------------------------------------------------------------------------
class Post extends Model implements Visitable
{
    use InteractsWithVisits;

    // ...
}

// Get all statistic data from eager loading
Post::with('visit_statistics')->get();
Post::find($id)->with('visit_statistics')->get();
$posts = Post::with('visit_statistics')->orderByVisits()->limit(50)->paginate(10);

// Scope order by column "visit_total_without_crawlers"
Post::orderByVisits()->get(); // descending
Post::orderByVisitsAsc()->get(); // ascending


// GET RESULTING STATISTICS FOR ONE MODEL
// -----------------------------------------------------------------------------

// Sum visits from all catagories
visit_statistisc($post)->visitsPerson();
// Or only one category
visit_statistisc($post, VisitorCategory::WEB)->visitsPerson();
// Or sum visits from list of categories
visit_statistisc($post, [VisitorCategory::WEB, VisitorCategory::API])->visitsPerson();

// another type counters
visit_statistisc($post)->visitsCrawler();
visit_statistisc($post)->visitsTotal();
visit_statistisc($post)->visitsYesterday();
visit_statistisc($post)->visitsThisWeek();
visit_statistisc($post)->visitsThisMonth();
visit_statistisc($post)->visitsLastYear();


// lists of data
visit_lists($post, VisitorCategory::WEB)->dailyNumbers();
visit_lists($post, VisitorCategory::WEB)->weeklyNumbers();
visit_lists($post, VisitorCategory::WEB)->monthlyNumbers();
visit_lists($post, VisitorCategory::WEB)->annualNumbers();
visit_lists($post, VisitorCategory::WEB)->countries();
visit_lists($post, VisitorCategory::WEB)->languages();
visit_lists($post, VisitorCategory::WEB)->operatingSystems();


// GET RESULTING STATISTICS FOR MODEL TYPE
// -----------------------------------------------------------------------------

visit_statistisc(Post::class)->sumaryPerson();
// OR
visit_statistisc(new Post())->sumaryPerson();
// OR
visit_statistisc('App\Post')->sumaryPerson();

// another type sumary
visit_statistisc(Post::class)->sumaryCrawler();
visit_statistisc(Post::class)->sumaryTotal();
visit_statistisc(Post::class)->sumaryYesterday();
visit_statistisc(Post::class)->sumaryThisWeek();
visit_statistisc(Post::class)->sumaryThisMonth();
visit_statistisc(Post::class)->sumaryLastYear();


// Remove visits on delete model
// -----------------------------------------------------------------------------
class Post extends Model implements Visitable
{
    use InteractsWithVisits;

	protected $removeVisitsOnDelete = true;

	// ...
}
