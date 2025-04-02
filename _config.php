<?php

namespace Werkbot\Search;

use SilverStripe\SiteConfig\SiteConfig;

SiteConfig::add_extension(SearchSiteConfigExtension::class);

// Get the root directory dynamically
$folderPath = dirname(__DIR__, 3) . '/search';

// Create the folder if it doesn't exist
if (!is_dir($folderPath)) {
  mkdir($folderPath, 0755, true);
}
