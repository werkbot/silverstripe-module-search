<?php

namespace Werkbot\Search;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Werkbot\Search\Tasks\SearchIndex;

class DatabaseAdminExtension extends Extension
{
  public function onAfterBuild($output)
  {
    $searchIndex = SearchIndex::create();

    $searchIndex->run(
        new ArrayInput(
          [],
          new InputDefinition($searchIndex->getOptions())
        ),
        $output
      );
  }
}

