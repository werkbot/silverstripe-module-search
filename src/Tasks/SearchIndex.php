<?php

namespace Werkbot\Search\Tasks;

use SilverStripe\PolyExecution\PolyOutput;
use Override;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\DB;
use Symfony\Component\Console\Input\InputInterface;
use Werkbot\Search\Helpers\TNTSearchHelper;
use Werkbot\Search\SearchableExtension;

class SearchIndex extends BuildTask
{
  protected string $title = "Search Index";
  protected static string $description = "Index DataObjects with SearchableExtension";
  protected $enabled = true;

  #[Override]
  public function execute(InputInterface $input, PolyOutput $output): int
  {
    if (!file_exists(dirname(__DIR__, 5) . '/search')) {
      mkdir(dirname(__DIR__, 5) . '/search');
      echo "Created search folder<br /><br />";
    }
    $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
    $classes = ClassInfo::classesWithExtension(SearchableExtension::class);
    foreach ($classes as $className) {
      $searchableClass = singleton($className);
      if ($query = $searchableClass->getIndexQuery()) {
        DB::alteration_message('Indexing...' . $className, 'created');
        $indexer->query($query);
        $indexer->run();
      }
    }
  }
}
