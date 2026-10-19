<?php

namespace Werkbot\Search\SearchQueries;

use Override;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\Model\ArrayData;
use SilverStripe\ORM\DB;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;

class CustomSideReport_SearchQuery extends Report
{
  #[Override]
  public function title()
  {
    return 'Search Query Report';
  }

  #[Override]
  public function description()
  {
    $desc = 'Shows search queries';

    return $desc;
  }

  public function sort()
  {
    return 1;
  }

  #[Override]
  public function records($params = null)
  {
    if ($params){
      $StartDate = $params['StartDate']." 00:00:00";
      $EndDate = $params['EndDate']." 23:59:50";
    } else {
      $StartDate = date('Y-m-d H:i:s', strtotime('-7 days'));
      $EndDate = date('Y-m-d')." 23:59:50";
    }

    $records = ArrayList::create();
    $sql = "
       SELECT
        Max(Created) as Created, Query, COUNT(*) AS QueryCount
      FROM
        SearchQuery
      WHERE
        Created BETWEEN '".$StartDate."' AND '".$EndDate."'
      GROUP BY
        Query
      ORDER BY
        Query ASC
    ";
    $results = DB::query($sql);

    foreach($results as $row){
      $records->push(ArrayData::create([
        'Created' => $row['Created'],
        'Query' => $row['Query'],
        'QueryCount' => $row['QueryCount']
      ]));
    }

    return $records;
  }

  public function sourceRecords($params = null)
  {
    $params = ($_REQUEST['filters'] ?? null);
    return $this->records($params);
  }

  #[Override]
  public function columns()
  {
    $fields = [
      'Created' => [
        "title" => 'Date'
      ],
      'Query' => [
        "title" => 'Query'
      ],
      'QueryCount' => [
        "title" => 'Count'
      ],
    ];

    return $fields;
  }

  public function parameterFields()
  {
    $today = date('Y-m-d');

    $StartDateField = DateField::create('StartDate','Start Date')
      ->setHTML5(true)
      ->setMinDate(date('Y-m-d', strtotime('-3 years')))
      ->setValue(date('Y-m-d', strtotime('-7 days')));

    $EndDateField = DateField::create('EndDate', 'End Date')
      ->setHTML5(true)
      ->setMaxDate($today)
      ->setValue($today);

    return FieldList::create(
      $StartDateField,
      $EndDateField
    );
  }
}

