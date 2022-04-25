## Usage

### DataObject Setup
Just add the extension:
```
DataObject::add_extension(\Werkbot\Search\SearchableExtension::class);
```

Then define `getIndexQuery` function on the DataObject:
```
public function getIndexQuery(){
  return "SELECT
      concat(\"CLASSNAME_\", ID) AS ID,
      ClassName,
      Title,
      Content
    FROM
      CLASSNAME";
}
```

In the query, the classname of the dataobject is added to the id, like so: `concat(\"CLASSNAME_\", ID) AS ID`
So it's the Classname of the DataObject, followed by an underscore, then the ID selector. This is used by the search function (`function SiteSearchFormResults`), it allows us to lookup the objects correctly.

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
