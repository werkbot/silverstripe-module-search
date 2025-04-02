<?php

namespace Werkbot\Search;

use SilverStripe\SiteConfig\SiteConfig;

SiteConfig::add_extension(SearchSiteConfigExtension::class);

// Create the search directory if it doesn't exist
if (!file_exists(dirname(__DIR__, 5) . '/search')) {
  mkdir(dirname(__DIR__, 5) . '/search');
}
