<?php

namespace Werkbot\Search\Tasks;

use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Werkbot\Search\Helpers\TNTSearchHelper;
use Werkbot\Search\SearchableExtension;

class SearchIndex extends BuildTask
{
  protected $title = "Search Index";
  protected $description = "Index DataObjects with SearchableExtension";
  protected $enabled = true;

  public function run($request)
  {
    if (!file_exists(dirname(__DIR__, 5) . '/search')) {
      mkdir(dirname(__DIR__, 5) . '/search');
      echo "Created search folder<br /><br />";
    }

    $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
    $classes = ClassInfo::classesWithExtension(SearchableExtension::class);

    $query = '';
    foreach ($classes as $title => $className) {
      $searchableClass = singleton($className);
      if ($classQuery = $searchableClass->getIndexQuery()) {
        // Remove semi-colon if it exists
        $classQuery = rtrim($classQuery, ';');

        $query .= $classQuery . ' UNION ALL ';

        DB::alteration_message('Indexing...' . $className, 'created');
      }
    }
    $query = str_replace('"', "'", $query);

    // Remove last " UNION ALL "
    $query = substr($query, 0, -11);

    $indexer->query($query);
    $indexer->run();
  }

}
