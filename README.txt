=== Fantastic ElasticSearch ===
Contributors: parisholley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paris%40holleywoodproductions%2ecom&lc=US&item_name=Paris%20Holley&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Tags: search,performance,elastic search,elastic,elasticsearch,facet,faceting,faceted search
Requires at least: 3.5
Tested up to: 4.5.1
Stable tag: 4.1.0

Improve wordpress search performance/accuracy and enable faceted search by leveraging an ElasticSearch server.

== Description ==

This plugin is NOT a simple drop-in, it is expected you have some understanding of what an ElasticSearch server is and how it works. The goals/features of this plugin are:

* Replace default wordpress search functionality with that of an ElasticSearch server.
* Ability to specify what data points should be indexed and what the relevancy of those points are.
* Fall back to default wordpress search if ElasticSearch server is not responsive.
* Update ElasticSearch server when posts are removed/added/unpublished.
* Provide a faceting API for building your own custom searches

Please submit bugs or contributions to the github location and not on wordpress' system: 

https://github.com/parisholley/wordpress-fantastic-elasticsearch/

API/filter/faceting documentation can be found on the wiki:

https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki

== Installation ==

1. Upload plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click the 'ElasticSearch' menu item and follow the instructions on each section to configure the plugin. (be sure to save on each section)
4. Select "Enable" on "Server Settings" when you are ready for it to go live. 

== Screenshots ==

1. Configure your ElasticServer settings
2. Determine what data you want to index
3. Alter the result scoring behavior
4. Wipe and re-index data is available if needed

== Changelog ==

= 4.1.0 =
* support for secondary index to prevent wiping production data live

= 4.0.6 =
* allow custom facets to be filtered when searching
* allow child taxonomies to be faceted when on taxonomy page
* allow or faceting to filter counts on unrelated facets for a more intuitive interface

= 4.0.5 =
* bug fix

= 4.0.4 =
* Support for using facet API when providing custom data
* Control whether parent categories are included when indexed
* Additional filters

= 4.0.3 =
* Exact matches in title, etc will rank higher now in results

= 4.0.2 =
* post_type should not be analyzed

= 4.0.1 =
* Fixed bugs in admin

= 4.0.0 =
* Tested against Wordpress 4.5.1
* Upgrade Elastica to latest api, this plugin now requires ElasticSearch 2.x

= 3.1.1 =
* Updated to latest composer

= 3.1.0 =
* Better OR behavior when faceting is involved
* Expanded widget options to allow for AJAX based faceting. See github wiki for help on using this.
* Fixed bug where hooks weren't working on category pages when permalinks are on
* Removed dependency on purl due to memory leak
* Added filters so devs can hook into NHP options (thanks to nielo)
* Expanded ES integration to taxonomy, tag, and custom post archives
* More strict fixes (thanks to markoheijnen and michaelsauter)
* Support for indexing meta fields (thanks to schorsch)
* Ability to facet on post_type
* Improved searching results (assumes English language, need to expand this in future)

= 3.0.0 =
* Tested against Wordpress 4.0
* Tested against ElasticSearch 1.3 (Make sure you test before upgrading, in theory, old versions should work just fine)
* Fix broken category integration

= 2.1.0 =
* Added some error messages if user has bad config or changed taxonomy
* Category pages now sort by date whereas search is by relevance
* Support for exact phrases in search, ie: "search these words exactly"
* Support for boolean and fuzzy search syntax "term1 AND term2", "howdoyaspellthis~ words i can spell"
* Support multiple category syntax on wordpress (?cat=2,3) (thanks markoheijnen)
* Remove HTML from post_content so tags and metadata don't influence search results (thanks michaelsauter)
* Created simple widgets for showing faceting options on search/category
* Fixed bug where facets weren't filtering based on currently viewed category
* Searching now will search taxonomy names instead of slugs (faceting still uses slugs)

= 2.0.2 =
* Fixed bug where post with an id = 1 would show when no results were returned from ElasticSearch (thanks michaelsauter)

= 2.0.1 =
* Preventing more notices (thanks michaelsauter)

= 2.0.0 =
* WARNING: This is a major release, any custom work (API, faceting) may not work. If you have not customized, you should have a flawless upgrade. Please test in a development environment first and report any problems.
* Large cleanup effort
* Unit tests to adhere to STRICT 5.3, 5.4, 5.5 compatability
* Integeration tests to ensure intended behavior with ElasticSearch 0.20.6 and 0.90.2
* Continuous integration setup with travis-ci
* Developer documentation for faceting and extending

= 1.2.4 =
* Allow plugin to work if downloaded from github or wordpress (images weren't showing up in admin)

= 1.2.3 = 
* Bug fixes as result of merge

= 1.2.2 =
* Better coding practices (PHP strict support, etc) by both eedeebee and deltamualpha
* Fixes for certain types of wordpress/server configurations

= 1.2.1 =
* Fix if ranges aren't defined but marked as numeric

= 1.2.0 =
* Faceting API to make URL management and customer interfaces easier
* New field mapping page to allow for custom field settings (currently only for setting up ranges)

= 1.1.8 =
* Bug fix from merge

= 1.1.7 =
* Merged pull requests that added more hooks/filters (thanks to turcottedanny)

= 1.1.6 =
* Fixed search query not showing in title (thanks to eleshar for finding)

= 1.1.5 =
* Removed NHP warning (thanks to EkAndreas)
* Fixed issue with plugin indexing post types that weren't selected

= 1.1.4 =
* Fixed bug that caused search to show invalid results due to wordpress filtering on top of elastic search resutls.

= 1.1.3 =
* Fixed bug that would only search content with a specific type (php scope creep).

= 1.1.2 =
* Ability to specify which categories should be enabled

= 1.1.1 =
* Fixed class load problem due to case sensitivity
* Removed duplicate enable setting 

= 1.1.0 =
* Uses AJAX calls to index all documents to get around server execution timeout
* Support for using ElasticSearch on category page
* Faceting documentation
* Show more post types in admin
* Only index published posts, need to test other status behavior (like future)
* Support for boolean faceting (and/or)

= 1.0.2 =

* Possible fix for reported class not found problem
* Ignore connection failure during validation

= 1.0.1 =

* Fixed bug, forgot to convert Api to use defaults

= 1.0.0 =

* Initial release
