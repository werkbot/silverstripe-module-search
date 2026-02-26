<?php

namespace Werkbot\Search;

use SilverStripe\Core\Extension;
use SilverStripe\Control\Controller;
use Werkbot\Search\Tasks\SearchIndex;

class DatabaseAdminExtension extends Extension
{
  public function onAfterBuild()
  {
    (SearchIndex::create())->run(Controller::curr()->getRequest());
  }
}

