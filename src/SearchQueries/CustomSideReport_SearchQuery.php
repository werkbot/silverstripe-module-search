<?php

namespace Werkbot\Search\SearchQueries;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Reports\Report;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;

class CustomSideReport_SearchQuery extends Report
{
  public function title()
  {
    return 'Search Query Report';
  }

  public function description()
  {
    $desc = 'Shows search queries';

    return $desc;
  }

  public function sort()
  {
    return 1;
  }

  public function records($params = null)
  {
    if ($params){
      $StartDate = $params['StartDate']." 00:00:00";
      $EndDate = $params['EndDate']." 23:59:50";
    } else {
      $StartDate = date('Y-m-d H:i:s', strtotime('-7 days'));
      $EndDate = date('Y-m-d')." 23:59:50";
    }

    $records = new ArrayList();
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
      $records->push(new ArrayData(
        array(
          'Created' => $row['Created'],
          'Query' => $row['Query'],
          'QueryCount' => $row['QueryCount']
        )
      ));
    }

    return $records;
  }

  public function sourceRecords($params = null)
  {
    $params = ((isset($_REQUEST['filters'])) ? $_REQUEST['filters'] : null);
    return $this->records($params);
  }

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

