<?php
/**/
namespace Werkbot\Search;
/**/
use SilverStripe\Core\ClassInfo;
use TeamTNT\TNTSearch\TNTSearch;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\DataExtension;
/**/
class SearchableExtension extends DataExtension {
  /**/
  private $SearchableExtension_Title = "Title";
  private $SearchableExtension_Content = "Content";
  /**
   * getIndexQuery
   *
   * @return string
   */
  public function getIndexQuery(){
    return false;
  }
  /**
   * insertIndex
   *
   * @return void
   */
  public function insertIndex(){
    // Can these be defined in the object? Maybe we need to store in yml
    $this->owner->SearchableExtension_Title = "Title";
    $this->owner->SearchableExtension_Content = "Content";

    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->insert([
      'ID' => ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID,
      'ClassName' => $this->owner->ClassName,
      $this->owner->SearchableExtension_Title => $this->owner->{$this->owner->SearchableExtension_Title},
      $this->owner->SearchableExtension_Content => $this->owner->{$this->owner->SearchableExtension_Content},
    ]);
  }
  /**
   * updateIndex
   *
   * @return void
   */
  public function updateIndex(){
    // Can these be defined in the object? Maybe we need to store in yml
    $this->owner->SearchableExtension_Title = "Title";
    $this->owner->SearchableExtension_Content = "Content";

    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->update(
      $this->owner->ID,
      [
        'ID' => ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID,
        'ClassName' => $this->owner->ClassName,
        $this->owner->SearchableExtension_Title => $this->owner->{$this->owner->SearchableExtension_Title},
        $this->owner->SearchableExtension_Content => $this->owner->{$this->owner->SearchableExtension_Content},
      ]
    );
  }
  /**
   * deleteIndex
   *
   * @return void
   */
  public function deleteIndex(){
    $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
    $index->delete(ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID);
  }
  /**
   * onBeforeWrite
   *
   * @return void
   */
	public function onBeforeWrite(){
    if($this->owner->isInDB()){
      if($this->owner->isChanged($this->owner->SearchableExtension_Title) || $this->owner->isChanged($this->owner->SearchableExtension_Content)){
        $this->owner->updateIndex();
      }
    }
  }
  /**
   * onAfterWrite
   *
   * @return void
   */
  public function onAfterWrite(){
    if($this->owner->isChanged('ID')){
      $this->owner->insertIndex();
    }
		parent::onAfterWrite();
	}
  /**
   * onAfterUnpublish
   *
   * @return void
   */
  public function onAfterUnpublish() {
    $this->owner->deleteIndex();
  }
  /**
   * onAfterDelete
   *
   * @return void
   */
  public function onAfterDelete() {
    $this->owner->deleteIndex();
  }
}
