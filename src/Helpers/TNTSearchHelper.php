<?php

namespace Werkbot\Search;

use TeamTNT\TNTSearch\TNTSearch;
use SilverStripe\Core\Environment;
/**/
class TNTSearchHelper{
  /*
    Call this method to return a singleton
  */
  public static function Instance(){
    static $inst = null;
    if ($inst === null) {
      $inst = new TNTSearchHelper();
    }
    return $inst;
  }
  /**
   * getTNT
   *
   * @return TNTSearch
   */
  public function getTNTSearch(){
    $tnt = new TNTSearch;
    $tnt->loadConfig([
      'driver'    => 'mysql',
      'host'      => Environment::getEnv('SS_DATABASE_SERVER'),
      'database'  => Environment::getEnv('SS_DATABASE_NAME'),
      'username'  => Environment::getEnv('SS_DATABASE_USERNAME'),
      'password'  => Environment::getEnv('SS_DATABASE_PASSWORD'),
      'storage'   => dirname(__DIR__, 5).'\search',
      'stemmer'   => \TeamTNT\TNTSearch\Stemmer\PorterStemmer::class
    ]);
    return $tnt;
  }

  /**
   * getTNTSearch_CreateIndex
   *
   * @return TNTIndexer
   */
  /*public function getTNTSearch_CreateIndex(){
    $tnt = $this->getTNTSearch();
    $indexer = $tnt->createIndex('site.index');
    $indexer->setPrimaryKey('ID');
    return $indexer;
  }*/

  /**
   * getTNTSearch_SelectIndex
   *
   * @return TNTIndexer
   */
  public function getTNTSearchIndex($create=false){
    $tnt = $this->getTNTSearch();
    if($create){
      $indexer = $tnt->createIndex('site.index');
    }else{
      $tnt->selectIndex('site.index');
      $indexer = $tnt->getIndex();
    }
    $indexer->setPrimaryKey('ID');
    return $indexer;
  }


}
