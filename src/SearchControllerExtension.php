<?php
/**/
namespace Werkbot\Search;
/**/
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
use TeamTNT\TNTSearch\Exceptions\IndexNotFoundException;

/**/
class SearchControllerExtension extends DataExtension {
	/**/
  private static $allowed_actions = [
    "SiteSearchForm",
    "SiteSearchFormResults",
  ];
  /**
   * Site search form
   *
   * @return SiteSearchForm
   */
  public function SiteSearchForm(){
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
   */
  public function SiteSearchFormResults($searchdata, $form){
    $start = ($this->owner->getRequest()->getVar('start')) ? (int)$this->owner->getRequest()->getVar('start') : 0;
    $Results = new ArrayList();
    $ErrorMessge = "";

    if($searchdata['Search']){
      try{
        $tnt = TNTSearchHelper::Instance()->getTNTSearch();
        $tnt->selectIndex('site.index');
        $res = $tnt->search($searchdata['Search']);
        $classlist = [];
        $classes = ClassInfo::classesWithExtension(SearchableExtension::class);
        foreach($classes as $key => $value){
          $classlist[ClassInfo::shortName($value)] = $value;
        }
        foreach($res["ids"]  as $result){
          $parts = explode("_", $result);
          if($obj = $classlist[$parts[0]]::get()->byID($parts[1])){
            $Results->push($obj);
          }
        }
      }catch(IndexNotFoundException $e) {
        $validationResult = new ValidationResult();
        $validationResult->addFieldError('Message', 'Search index not found');
        $form->setSessionValidationResult($validationResult);
      }
    }

    $this->owner->extend("updateSiteSearchFormResults", $searchdata, $form, $Results);

    // Pack up the results
    $Paged = new PaginatedList($Results, $this->owner->getRequest());
    $Paged->setPageLength(10);
    $Paged->setPageStart($start);
    $data = array(
      'Results' => $Paged,
      'Query' => DBField::create_field('Text', $form->getSearchQuery()),
      'Title' => _t('SilverStripe\\CMS\\Search\\SiteSearchForm.SearchResults', 'Search Results')
    );
    return $this->owner->customise($data)->renderWith(array('SearchableResultsPage', 'Page'));
  }
}
