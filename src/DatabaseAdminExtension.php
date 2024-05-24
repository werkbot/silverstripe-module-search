<?php

namespace Werkbot\Search;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;
use Werkbot\Search\Tasks\SearchIndex;

class DatabaseAdminExtension extends DataExtension
{
  public function onAfterBuild()
  {
    (new SearchIndex())->run(Controller::curr()->getRequest());
  }
}

