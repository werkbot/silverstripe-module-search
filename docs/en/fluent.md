# Using with the Fluent Module
## Separate Search Indexes for Each Language
When using the fluent module for site translations, it is often desireable to have separate search indexes for each language.
```php
<?php

use SilverStripe\ORM\DataExtension;
use TractorCow\Fluent\State\FluentState;

class TNTSearchHelperExtension extends DataExtension
{
  public function updateSearchIndexName(&$name)
  {
    $locale = FluentState::singleton()->withState(function (FluentState $state) {
      return $state->getLocale();
    });

    $name = $locale . '-site.index';
  }
}
```

```yml
Werkbot\Search\Helpers\TNTSearchHelper:
  extensions:
    - TNTSearchHelperExtension
```

This will create a separate index for each locale, with the locale code as a prefix. For example, if you have two locales "en" and "fr", you will have two indexes: "en-site.index" and "fr-site.index".

You can produce each file by switching the locale in the CMS and running the search index dev task.
```
php vendor/silverstripe/framework/cli-script.php /dev/tasks/Werkbot-Search-Tasks-SearchIndex
```
## Localised Index Queries
When you have separate indexes for each language, you will also need to modify your search index queries to use the correct locale content.
```php
public function getIndexQuery()
{
  $class = get_class($this);
  $class = str_replace('\\', '\\\\', $class);

  $locale = FluentState::singleton()->withState(function (FluentState $state) {
    return $state->getLocale();
  });

  return <<<SQL
    SELECT * FROM (
      SELECT
        concat("Page_", SiteTree_Live.ID) AS ID,
        SiteTree_Live.ClassName,
        SiteTree_Localised_Live.Title,
        CONCAT_WS(
          ' ',
          SiteTree_Localised_Live.Title,
          SiteTree_Localised_Live.MetaDescription,
          Page_Localised_Live.Desc,
          Page_Localised_Live.ContentColumnTwo,
          REGEXP_REPLACE(SiteTree_Localised_Live.Content, '<[^>]*>+', ''),
          REGEXP_REPLACE(GROUP_CONCAT(ContentLayoutHtml_Localised_Live.Content), '<[^>]*>+', '')
        ) AS Content
      FROM
        SiteTree_Live
      LEFT JOIN
        SiteTree_Localised_Live
      ON
        SiteTree_Localised_Live.RecordID = SiteTree_Live.ID
      LEFT JOIN
        Page_Localised_Live
      ON
        Page_Localised_Live.RecordID = SiteTree_Live.ID
      LEFT JOIN
        ContentLayout_Live
      ON
        ContentLayout_Live.PageID = Page_Localised_Live.RecordID
      LEFT JOIN
        ContentLayoutHtml_Localised_Live
      ON
        ContentLayoutHtml_Localised_Live.RecordID = ContentLayout_Live.ID
      WHERE
        SiteTree_Live.ShowInSearch = '1'
      AND
        SiteTree_Live.ClassName = '$class'
      AND
        SiteTree_Localised_Live.Locale = '$locale'
      AND
        Page_Localised_Live.Locale = '$locale'
      AND (
        ContentLayoutHtml_Localised_Live.Locale = '$locale'
        OR
        -- If no ContentLayoutHtmls exist on the Page we still index it
        ContentLayoutHtml_Localised_Live.Locale IS NULL
      )
      GROUP BY
        Page_Localised_Live.RecordID
    ) AS BASE
    WHERE
      Content != '';
  SQL;
}
```

