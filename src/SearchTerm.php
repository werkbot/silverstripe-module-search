<?php

namespace Werkbot\Search;

use SilverStripe\ORM\DataObject;

class SearchTerm extends DataObject
{
  private static $singular_name = 'Search Term';
  private static $plural_name = 'Search Terms';
  private static $table_name = 'SearchTerm';

  private static $db = [
    'SearchTermText' => 'Text',
    'SortOrder' => 'Int',
  ];

  private static $has_one = [
    'SearchTermOf' => DataObject::class,
  ];
}
