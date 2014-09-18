# Fantastic ElasticSearch

[![build status](https://travis-ci.org/parisholley/wordpress-fantastic-elasticsearch.svg?branch=3.0)](https://travis-ci.org/parisholley/wordpress-fantastic-elasticsearch)

Improve wordpress search performance/accuracy and enable faceted search by leveraging an ElasticSearch server.

## Description

This plugin is NOT a simple drop-in, it is expected you have some understanding of what an ElasticSearch server is and how it works. The goals/features of this plugin are:

* Replace default wordpress search functionality with that of an ElasticSearch server.
* Ability to specify what data points should be indexed and what the relevancy of those points are.
* Fall back to default wordpress search if ElasticSearch server is not responsive.
* Update ElasticSearch server when posts are removed/added/unpublished.
* Provide a faceting API for building your own custom searches

## Faceting

API/filter/faceting documentation can be found on the wiki:

https://github.com/parisholley/wordpress-fantastic-elasticsearch/wiki

## Installation

1. Upload plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click the 'ElasticSearch' menu item and follow the instructions on each section to configure the plugin. (be sure to save on each section)
4. Select "Enable" on "Server Settings" when you are ready for it to go live.