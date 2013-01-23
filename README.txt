=== Fantastic ElasticSearch ===
Contributors: parisholley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=paris%40holleywoodproductions%2ecom&lc=US&item_name=Paris%20Holley&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest
Tags: search,performance,elastic search,elastic,elasticsearch
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

== Changelog ==

= 1.0.0 =

* Initial release
