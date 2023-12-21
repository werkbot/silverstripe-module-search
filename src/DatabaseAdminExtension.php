<?php

namespace Werkbot\Search;

use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

class DatabaseAdminExtension extends DataExtension
{
  public function onAfterBuild()
  {
    (new SearchIndex())->run(Controller::curr()->getRequest());
  }
}

