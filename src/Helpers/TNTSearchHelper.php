<?php

namespace Werkbot\Search\Helpers;

use SilverStripe\Core\Environment;
use SilverStripe\Core\Extensible;
use TeamTNT\TNTSearch\Stemmer\PorterStemmer;
use TeamTNT\TNTSearch\TNTSearch;
use Werkbot\Search\Engines\SqliteEngine_WithStringID;

class TNTSearchHelper
{
  use Extensible;

  /*
    Call this method to return a singleton
  */
  public static function Instance()
  {
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
  **/
  public function getTNTSearch()
  {
      $tnt = new TNTSearch;
      $tnt->loadConfig([
        'driver'    => 'mysql',
        'host'      => Environment::getEnv('SS_DATABASE_SERVER'),
        'database'  => Environment::getEnv('SS_DATABASE_NAME'),
        'username'  => Environment::getEnv('SS_DATABASE_USERNAME'),
        'password'  => Environment::getEnv('SS_DATABASE_PASSWORD'),
        'storage'   => dirname(__DIR__, 5).'/search',
        'stemmer'   => PorterStemmer::class,
        'engine'    => SqliteEngine_WithStringID::class,
      ]);
      return $tnt;
  }

  /**
   * getTNTSearch_SelectIndex
   *
   * @return TNTIndexer
  **/
  public function getTNTSearchIndex($create = false)
  {
      $tnt = $this->getTNTSearch();
      $indexName = $this->getIndexName();
      if ($create) {
          $indexer = $tnt->createIndex($indexName);
      } else {
          $tnt->selectIndex($indexName);
          $indexer = $tnt->getIndex();
      }
      $indexer->setPrimaryKey('ID');
      return $indexer;
  }

  public function getIndexName()
  {
    $name = 'site.index';
    $this->extend('updateSearchIndexName', $name);
    return $name;
  }

}

