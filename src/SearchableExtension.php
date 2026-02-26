<?php

namespace Werkbot\Search;

use SilverStripe\Core\Extension;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use Symbiote\GridFieldExtensions\GridFieldAddNewInlineButton;
use Symbiote\GridFieldExtensions\GridFieldEditableColumns;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;
use Werkbot\Search\Helpers\TNTSearchHelper;

class SearchableExtension extends Extension
{
  private static $has_many = [
    'SearchTerms' => SearchTerm::class . '.SearchTermOf',
  ];

  private static $owns = [
    'SearchTerms',
  ];

  /*
    Column names for the "Title" and "Content" search fields
    Override these to set them to a different column name
  */
  public $SearchableExtension_Title_ColumnName = "Title";
  public $SearchableExtension_Summary_ColumnName = "Content";

  private static $casting = [
    "getSearchableTitle" => "Text",
    "getSearchableSummary" => 'HTMLText',
  ];

  /**
   * updateCMSFields
   * Adds the SearchTerms GridField to the CMS tab
   * This should only be applied to Data objects
   *
   * @param FieldList $fields
   * @return void
   **/
  public function updateCMSFields(FieldList $fields)
  {
    if (DataObject::getSchema()->baseDataClass($this->getOwner()->ClassName) != SiteTree::class) {
      $this->addSearchSettingFields($fields);
    }
  }
  /**
   * updateSettingsFields
   * Adds the SearchTerms GridField to the settings tab
   * This should only be applied to SiteTree objects
   *
   * @param FieldList $fields
   * @return void
   **/
  public function updateSettingsFields(FieldList $fields)
  {
    if (DataObject::getSchema()->baseDataClass($this->getOwner()->ClassName) == SiteTree::class) {
      $this->addSearchSettingFields($fields);
    }
  }

  public function addSearchSettingFields(FieldList &$fields)
  {
    $fields->addFieldToTab('Root', TabSet::create('Search', Tab::create('Main')));

    if ($this->getOwner()->hasField("ShowInSearch")) {
      $fields->removeByName('ShowInSearch');
      $ShowInSearch = CheckboxField::create("ShowInSearch", $this->getOwner()->fieldLabel('ShowInSearch'));
      $ShowInSearchGroup = FieldGroup::create(
        'Settings',
        $ShowInSearch
      );
      $fields->addFieldToTab('Root.Search.Main', $ShowInSearchGroup);
    }

    $SearchTermsGridField = GridField::create(
      'SearchTerms',
      'Enter Search Terms',
      $this->getOwner()->SearchTerms(),
      GridFieldConfig::create()
        ->addComponent(GridFieldButtonRow::create('before'))
        ->addComponent(GridFieldToolbarHeader::create())
        ->addComponent(GridFieldEditableColumns::create())
        ->addComponent(GridFieldDeleteAction::create())
        ->addComponent(GridFieldAddNewInlineButton::create())
        ->addComponent(new GridFieldOrderableRows('SortOrder'))
    );

    $SearchTermsGridField->getConfig()->getComponentByType(GridFieldEditableColumns::class)->setDisplayFields([
      'SearchTermText'  => fn($record, $column, $grid) => TextField::create($column)
        ->setAttribute('placeholder', 'Enter search term')
    ]);

    $fields->addFieldToTab('Root.Search.Main', $SearchTermsGridField);
  }

  /**
   * getIndexQuery
   * This query is used when building the index
   *
   * @return string|boolean - FALSE if not set
   * Example:
      SELECT
        concat(\"Page_\", SiteTree.ID) AS ID,
        SiteTree.ClassName,
        SiteTree.Title,
        SiteTree.Content
      FROM
        Page
      LEFT JOIN
        SiteTree
      ON
        SiteTree.ID = Page.ID
      LEFT JOIN
        SearchTerm
      ON
        SearchTerm.SearchTermOfID = Page.ID  AND SearchTerm.SearchTermOfClass = SiteTree.ClassName
      WHERE
        SiteTree.ShowInSearch = '1'"
      AND
        SiteTree.Content IS NOT NULL;
   **/
  public function getIndexQuery()
  {
    return false;
  }
  /**
   * getSearchableID
   * Returns the ID to be used in search results, for objects that are apart of a page this can be
   * overridden to return the Page ID - which can then be used to remove duplicates from search results
   *
   * @return int
   */
  public function getSearchableID()
  {
    return $this->getOwner()->ClassName . "_" . $this->getOwner()->ID;
  }

  /**
   * getSearchableTitle
   * Returns the title, to be used in search results
   * Override if Title uses a different variable name
   *
   * @return string
   **/
  public function getSearchableTitle()
  {
    if ($this->getOwner()->SearchableExtension_Title_ColumnName) {
      return $this->getOwner()->{$this->getOwner()->SearchableExtension_Title_ColumnName};
    } else {
      return $this->getOwner()->Title;
    }
  }

  /**
   * getSearchableTitleColumnName
   * Returns the name of the Title Column, "Title" is returned if the
   * SearchableExtension_Title_ColumnName is not overridden
   * @return string
   **/
  public function getSearchableTitleColumnName()
  {
    if ($this->getOwner()->SearchableExtension_Title_ColumnName) {
      return $this->getOwner()->SearchableExtension_Title_ColumnName;
    } else {
      return "Title";
    }
  }

  /**
   * getSearchableSummary
   * Returns the content to be used in search results
   * Override if Content uses a different variable name
   *
   * @return string
   **/
  public function getSearchableSummary()
  {
    $content = "";
    if ($this->getOwner()->SearchableExtension_Summary_ColumnName) {
      $content = $this->getOwner()->{$this->getOwner()->SearchableExtension_Summary_ColumnName};
    } else {
      $content = $this->getOwner()->Content;
    }

    $this->getOwner()->extend('updateSearchableSummary', $content);

    return $content;
  }

  /**
   * getSearchableContent
   * Returns the content to be used when indexing this record
   * Override if Content uses a different variable name or to include more searchable content:
   *
   * Example, the specified column data from all child records will be concatendated to the searchable content:
   * return $this->Content . implode(',', $this->Children()->column('SearchTerm'));
   *
   * @return string
   **/
  public function getSearchableContent()
  {
    $content = "";
    foreach ($this->getOwner()->SearchTerms() as $term) {
      $content .= $term->SearchTermText . " ";
    }
    if ($this->getOwner()->SearchableExtension_Summary_ColumnName) {
      $content .= $this->getOwner()->{$this->getOwner()->SearchableExtension_Summary_ColumnName};
    } else {
      $content .= $this->getOwner()->Content;
    }

    return $content;
  }

  /**
   * getSearchableSummaryColumnName
   * Returns the name of the Summary Column, "Content" is returned if the
   * SearchableExtension_Summary_ColumnName is not overridden
   * @return string
   **/
  public function getSearchableSummaryColumnName()
  {
    if ($this->getOwner()->SearchableExtension_Summary_ColumnName) {
      return $this->getOwner()->SearchableExtension_Summary_ColumnName;
    } else {
      return "Content";
    }
  }

  /**
   * insertIndex
   *
   * @return void
   **/
  public function insertIndex()
  {
    $content = $this->getOwner()->getSearchableContent();
    if (!$content) {
      return;
    }

    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->insert([
      'ID' => ClassInfo::shortName($this->getOwner()->ClassName) . "_" . $this->getOwner()->ID,
      'ClassName' => $this->getOwner()->ClassName,
      'Title' => $this->getOwner()->getSearchableTitle(),
      'Content' => $content,
    ]);
  }

  /**
   * updateIndex
   *
   * @return void
   **/
  public function updateIndex()
  {
    $content = $this->getOwner()->getSearchableContent();
    if (!$content) {
      return;
    }

    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->update(
      ClassInfo::shortName($this->getOwner()->ClassName) . "_" . $this->getOwner()->ID,
      [
        'ID' => ClassInfo::shortName($this->getOwner()->ClassName) . "_" . $this->getOwner()->ID,
        'ClassName' => $this->getOwner()->ClassName,
        'Title' => $this->getOwner()->getSearchableTitle(),
        'Content' => $content,
      ]
    );
  }

  /**
   * deleteIndex
   *
   * @return void
   **/
  public function deleteIndex()
  {
    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->delete(ClassInfo::shortName($this->getOwner()->ClassName) . "_" . $this->getOwner()->ID);
  }

  /**
   * onBeforeWrite
   *
   * @return void
   **/
  public function onBeforeWrite()
  {
    if ($this->getOwner()->isInDB() && !$this->getOwner()->hasExtension(Versioned::class)) {
      if ($this->getOwner()->isChanged($this->getOwner()->SearchableExtension_Title_ColumnName) || $this->getOwner()->isChanged($this->getOwner()->SearchableExtension_Summary_ColumnName)) {
        $this->getOwner()->updateIndex();
      }
    }
  }

  /**
   * onAfterWrite
   *
   * @return void
   **/
  public function onAfterWrite()
  {
    if ($this->getOwner()->isChanged('ID') && !$this->getOwner()->hasExtension(Versioned::class)) {
      $this->getOwner()->insertIndex();
    }
  }

  /**
   * onBeforePublish
   *
   * @return void
   **/
  public function onBeforePublish()
  {
    if (!$this->getOwner()->isPublished()) {
      $this->getOwner()->insertIndex();
    }
  }

  /**
   * onAfterPublish
   *
   * @return void
   **/
  public function onAfterPublish()
  {
    $this->getOwner()->updateIndex();
  }

  /**
   * onAfterUnpublish
   *
   * @return void
   **/
  public function onAfterUnpublish()
  {
    $this->getOwner()->deleteIndex();
  }

  /**
   * onAfterDelete
   *
   * @return void
   **/
  public function onAfterDelete()
  {
      $this->getOwner()->deleteIndex();
  }

}

