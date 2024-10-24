<?php

namespace Werkbot\Search\SearchQueries;

use SilverStripe\ORM\DataObject;

class SearchQuery extends DataObject
{
  private static $singular_name = 'Search Query';
  private static $plural_name = 'Search Queries';
  private static $table_name = 'SearchQuery';

  private static $db = [
    'Query' => 'Text',
  ];
}

