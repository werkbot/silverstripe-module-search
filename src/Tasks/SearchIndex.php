<?php
/**/
namespace Werkbot\Search;
/**/
use SilverStripe\Dev\BuildTask;
use SilverStripe\Core\ClassInfo;
use Werkbot\Search\TNTSearchHelper;
/**/
class SearchIndex extends BuildTask{
  /**/
  protected $title = "Search Index";
  protected $description = "";
  protected $enabled = true;
  /**/
  public function run($request){
    $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
    $classes = ClassInfo::classesWithExtension("Werkbot\Search\SearchableExtension");
    foreach ($classes as $Title => $ClassName) {
      $searchableClass = singleton($ClassName);
      if($query = $searchableClass->getIndexQuery()){
        echo "Indexing...$ClassName<br />";
        $indexer->query($query);
        $indexer->run();
        echo "<br /><br />";
      }
    }
  }
}
