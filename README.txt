=== Fantastic ElasticSearch ===
Contributors: parisholley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paris%40holleywoodproductions%2ecom&lc=US&item_name=Paris%20Holley&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Tags: search,performance,elastic search,elastic,elasticsearch,facet,faceting,faceted search
Requires at least: 3.5
Tested up to: 3.5
Stable tag: trunk

Improve wordpress search performance and accuracy by leveraging an ElasticSearch server.

== Description ==

This plugin is NOT a simple drop-in, it is expected you have some understanding of what an ElasticSearch server is and how it works. The goals/features of this plugin are:

* Replace default wordpress search functionality with that of an ElasticSearch server.
* Ability to specify what data points should be indexed and what the relevancy of those points are.
* Fall back to default wordpress search if ElasticSearch server is not responsive.
* Update ElasticSearch server when posts are removed/added/unpublished.

Future Features

* Facet utilities for building your own faceted search (javascript components, etc)

Please submit bugs or contributions to the github location and not here on wordpress' system:

https://github.com/parisholley/wordpress-fantastic-elasticsearch/


== Installation ==

1. Upload `fantastic-elasticsearch` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click the 'ElasticSearch' menu item and follow the instructions on each section to configure the plugin.
4. Select "Enable" on "Server Settings" when you are ready for it to go live. 

== Frequently Asked Questions ==

= What happens if I change a taxonomy associate with a post? =

Rather than re-index every time taxonomies change, we expect that will be a manual effort on your part. This may be a configurable option in the future.

= How do I filter my search results using facets? =

By default, the plugin will look for those facet names (taxonomies) in the query string along with the search (ie: ?s=test&category[]=1). The more facets you add to the URL, the more it will filter. Facets values and counts are available at $WP_Query->facets.

== Screenshots ==

1. Configure your ElasticServer settings
2. Determine what data you want to index
3. Alter the result scoring behavior
4. Wipe and re-index data is available if needed

== Changelog ==

= 2.0.0 =
* Large cleanup effort
* Unit tests to adhere to STRICT 5.3, 5.4, 5.5 compatability
* Integeration tests to ensure intended behavior with ElasticSearch 0.20.6 and 0.90.2
* Continuous integration setup with travis-ci
* Developer documentation for faceting and extending

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
