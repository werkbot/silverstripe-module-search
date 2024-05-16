<?php

namespace Werkbot\Search;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;

class SearchSiteConfigExtension extends DataExtension
{
  private static $db = [
    'EnableBooleanSearch' =>  'Boolean',
  ];

  public function updateCMSFields(FieldList $fields)
  {
    $EnableBooleanSearchField = FieldGroup::create(
      'Search Settings',
      CheckboxField::create('EnableBooleanSearch', 'Enable boolean search?')
    );
    $fields->addFieldToTab("Root.Search", $EnableBooleanSearchField);
  }
}
