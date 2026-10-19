<?php

namespace Werkbot\Search\Tasks;

use Override;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Dev\BuildTask;
use SilverStripe\ORM\Connect\DatabaseException;
use SilverStripe\ORM\DB;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    $debugMode = $input->getOption('debug');

    $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
    $classes = ClassInfo::classesWithExtension(SearchableExtension::class);

    $query = '';
    foreach ($classes as $className) {
      $searchableClass = singleton($className);
      if ($classQuery = $searchableClass->getIndexQuery()) {
        // Remove semi-colon if it exists
        $classQuery = rtrim($classQuery, ';');
        $classQuery = str_replace('"', "'", $classQuery);

        if ($debugMode) {
          // Ensure this query is valid by running it before adding it to the indexer
          try {
            DB::query($classQuery);
          } catch (DatabaseException $e) {
            echo "<br>Error in query for class $className: " . $e->getMessage() . '<br>';
            return Command::FAILURE;
          }
        }

        $query .= $classQuery . ' UNION ALL ';

        DB::alteration_message('Indexing...' . $className, 'created');
      }
    }
    $query = str_replace('"', "'", $query);

    // Remove last " UNION ALL "
    $query = substr($query, 0, -11);

    $indexer->query($query);
    $indexer->run();

    return Command::SUCCESS;
  }

    public function getOptions(): array
    {
      return [
        new InputOption('debug', null, InputOption::VALUE_NONE, 'Debug'),
      ];
    }

}
