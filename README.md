# Silverstripe TNTSearch

A silverstripe search module that utilizes TNTSearch to index content.

## Installation
```
composer require werkbot/werkbot-search
```

#### Requirements
- https://github.com/teamtnt/tntsearch

## Setup
Add the following extensions to Page
```
Page::add_extension(SearchableExtension::class);
PageController::add_extension(SearchControllerExtension::class);
```

You will need to run `dev/build`

### Define getIndexQuery on Page
The `Page::class` will need to have a function `getIndexQuery` defined. Here is an example for Page:
```
/*
  Get Index Query
  Query used by search extension for indexing
*/
public function getIndexQuery(){
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
      SiteTree.ShowInSearch = '1'";
}
```
This is a simple query that is used by the indexer to index your content.

This function can be customized however you like and also can be added to DataObjects.

### DataObject Setup
Just add the extension:
```
DataObject::add_extension(\Werkbot\Search\SearchableExtension::class);
```

Then define `getIndexQuery` function on the DataObject:
```
public function getIndexQuery(){
  return "SELECT
      concat(\"CLASSNAME_\",ContentLayout.ID) AS ID,
      ClassName,
      Title,
      Content
    FROM
      CLASSNAME";
}
```

In the query, the classname of the dataobject is added to the id, like so: `concat(\"CLASSNAME_\", ID) AS ID`
So its the the Classname of the DataObject, followed by an underscore, then the ID selector. This is used by the search function (`function SiteSearchFormResults`), it allows us to lookup the objects correctly.

The object will also need to have a `Link` function defined that returns a link. This is used for the search results to link the result to a corresonding page. Here is an example of a dataobject that has a `has_one` relationship with `Page::class`:
```
/* This returns a link to the Parent Page */
public function Link(){
  return $this->Page()->Link();
}
```

## Search Index

The index is stored in the root directory of your project: `/search/site.index`

The index is initially created on a `dev/build`. Additional `dev/build`'s will update the index for all objects with the extension.

For all objects that have the extension applied, the index is updated on creation, edits/updates and delete/removals. So the index is always up to date.




