<?php

namespace Werkbot\Search;

use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\FormAction;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\PaginatedList;
use Werkbot\Search\TNTSearchHelper;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\CMS\Search\SearchForm;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\SiteConfig\SiteConfig;
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;

class SearchControllerExtension extends DataExtension
{
  /**
   * @config
   */
  private static $save_search_queries = true;

  private static $allowed_actions = [
    "SiteSearchForm",
    "SiteSearchFormResults",
  ];

  /**
   * Site search form
   *
   * @return SiteSearchForm
   **/
  public function SiteSearchForm()
  {
    $searchText = '';
    if ($this->owner->getRequest() && $this->owner->getRequest()->getVar('Search')) {
      $searchText = $this->owner->getRequest()->getVar('Search');
    }
    $fields = new FieldList(
      TextField::create('Search', _t("Search.INPUT_LABEL", "Search"), $searchText)
        ->setAttribute('placeholder', _t("Search.INPUT_PLACEHOLDER", "Enter search terms"))
        ->setAttribute('aria-label', _t("Search.INPUT_ARIALABEL", "Enter search terms"))
        ->setAttribute('title', _t("Search.INPUT_TITLE", "Search"))
    );
    $requried = new RequiredFields('Search');
    $actions = new FieldList(
      FormAction::create('SiteSearchFormResults', '')
        ->setUseButtonTag(true)
        ->setButtonContent(_t("Search.BUTTON_LABEL", 'Search'))
        ->setAttribute('aria-label', _t("Search.BUTTON_ARIALABEL", 'Search'))
    );
    $form = SearchForm::create($this->owner, 'SiteSearchForm', $fields, $actions, $requried);
    $form->setTemplate('Forms\\SiteSearchForm');
    $form->setFormAction('/home/SiteSearchForm');

    $this->owner->extend("updateSiteSearchForm", $fields, $required, $actions, $form);

    return $form;
  }

  /**
   * Process and render search results.
   *
   * @param array $data The raw request data submitted by user
   * @param SiteSearchForm $form The form instance that was submitted
   * @param HTTPRequest $request Request generated for this action
   **/
  public function SiteSearchFormResults($searchdata, $form)
  {
    $start = ($this->owner->getRequest()->getVar('start')) ? (int)$this->owner->getRequest()->getVar('start') : 0;
    $Results = new ArrayList();
    $ErrorMessge = "";

    if (isset($searchdata['Search'])) {
      try {
        $Results = $this->getSearchResults($searchdata['Search']);
      } catch (IndexNotFoundException $e) {
        $validationResult = new ValidationResult();
        $validationResult->addFieldError('Message', 'Search index not found');
        $form->setSessionValidationResult($validationResult);
      }

      if ($this->owner->config()->get('save_search_queries')) {
        // Store the Search Query
        $sq = SearchQuery::create();
        $sq->Query = $searchdata['Search'];
        $sq->write();
      }
    }

    $pageLength = 10;
    $this->owner->extend("updateSiteSearchFormResults", $searchdata, $form, $Results, $pageLength);

    // Pack up the results
    $Paged = new PaginatedList($Results, $this->owner->getRequest());
    $Paged->setPageLength($pageLength);
    $Paged->setPageStart($start);
    $data = array(
      'Results' => $Paged,
      'Query' => DBField::create_field('Text', $form->getSearchQuery()),
      'Title' => _t('SilverStripe\\CMS\\Search\\SiteSearchForm.SearchResults', 'Search Results')
    );
    return $this->owner->customise($data)->renderWith(array('SearchableResultsPage', 'Page'));
  }

  /**
   * Get search results
   *
   * @param string $search
   * @return ArrayList
   **/
  public function getSearchResults(string $search)
  {
    $results = ArrayList::create();

    $tnt = TNTSearchHelper::Instance()->getTNTSearch();
    $tnt->selectIndex('site.index');
    if (SiteConfig::current_site_config()->EnableBooleanSearch) {
      $res = $tnt->searchBoolean($search);
      //if no results with boolean search, do a regular search
      if (empty($res['ids'])) {
        $res = $tnt->search($search, 1000);
      }
    } else {
      $res = $tnt->search($search, 1000);
    }

    $classlist = [];
    $classes = ClassInfo::classesWithExtension(SearchableExtension::class);
    foreach ($classes as $key => $value) {
      $classlist[ClassInfo::shortName($value)] = $value;
    }

    if (isset($res["docScores"])) {
      $docScores = $res['docScores'];
      uasort($docScores, function ($a, $b) {
        return abs($a) < abs($b) ? 1 : -1;
      });
      foreach ($docScores as $id => $score) {
        if ($id) {
          $parts = explode("_", $id);
          if ($obj = $classlist[$parts[0]]::get()->byID($parts[1])) {
            $results->push($obj);
          }
        }
      }
    } else {
      foreach ($res['ids'] as $result) {
        if ($result) {
          $parts = explode("_", $result);
          if ($obj = $classlist[$parts[0]]::get()->byID($parts[1])) {
            $results->push($obj);
          }
        }
      }
    }

    $results->removeDuplicates('getSearchableID');

    return $results;
  }
}
