# Silverstripe TNTSearch
[![Latest Stable Version](http://poser.pugx.org/werkbot/werkbot-search/v)](https://packagist.org/packages/werkbot/werkbot-search) [![Total Downloads](http://poser.pugx.org/werkbot/werkbot-search/downloads)](https://packagist.org/packages/werkbot/werkbot-search) [![Latest Unstable Version](http://poser.pugx.org/werkbot/werkbot-search/v/unstable)](https://packagist.org/packages/werkbot/werkbot-search) [![License](http://poser.pugx.org/werkbot/werkbot-search/license)](https://packagist.org/packages/werkbot/werkbot-search) [![PHP Version Require](http://poser.pugx.org/werkbot/werkbot-search/require/php)](https://packagist.org/packages/werkbot/werkbot-search)

A silverstripe search module that utilizes TNTSearch to index content.

## Installation
```
composer require werkbot/werkbot-search
```

#### Requirements
- https://github.com/teamtnt/tntsearch

## Setup
Add the following extensions to Page
```php
Page::add_extension(SearchableExtension::class);
PageController::add_extension(SearchControllerExtension::class);
```

You will need to run `dev/build`

### Define getIndexQuery on Page
The `Page::class` will need to have a function `getIndexQuery` defined. Here is an example for Page:
```php
/**
 * getIndexQuery
 * This query is used when building the index
 *
 * @return string|boolean - FALSE if not set
 */
public function getIndexQuery()
{
  return "SELECT
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
    WHERE
      SiteTree.ShowInSearch = '1'"
    AND
      SiteTree.Content IS NOT NULL;
}
```
This is a simple query that is used by the indexer to index your content.

This function can be customized however you like and also can be added to DataObjects.

## Data Extensions
Note that if the `getIndexQuery` method is defined in a DataExtension, the `SearchableExtension` must be added before the extension that adds the method. Otherwise the method will return the default: false.
```php
SearchableDataObject::add_extension(SearchableExtension::class);  // MUST BE APPLIED FIRST for getIndexQuery to return SQL Query string.
SearchableDataObject::add_extension(SearchableDataObjectExtension::class); // Defines getIndexQuery SQL Query string.
```

## External Libraries
By default, the templates used here use classes provided by external css libraries. We suggest installing both for the best experience:
- [Werkbot Framewerk](https://www.npmjs.com/package/werkbot-framewerk) (CSS Libarary)
- [Font Awesome 6](https://fontawesome.com/) (Icon classes)

## Usage
* [Usage documentation](docs/en/README.md)
