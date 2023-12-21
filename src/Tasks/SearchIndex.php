<?php

namespace Werkbot\Search;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Werkbot\Search\TNTSearchHelper;

class SearchIndex extends BuildTask
{
  protected $title = "Search Index";
  protected $description = "";
  protected $enabled = true;

  public function run($request)
  {
    if (!file_exists(dirname(__DIR__, 5) . '/search')) {
      mkdir(dirname(__DIR__, 5) . '/search');
      echo "Created search folder<br /><br />";
    }
    $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
    $classes = ClassInfo::classesWithExtension(SearchableExtension::class);
    foreach ($classes as $title => $className) {
      $searchableClass = singleton($className);
      if ($query = $searchableClass->getIndexQuery()) {
        $indexer->query($query);
        $indexer->run();
        DB::alteration_message('Indexing...' . $className, 'created');
      }
    }
  }
}

