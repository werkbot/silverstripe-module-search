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


## Search Modal
Include a search modal component on your site. The modal is opened when `<% include SearchModalLink %>` is clicked. The modal is closed when its background is clicked or when the "Escape" key is pressed.

**Sass**
- Import the styles: `@import '../../vendor/werkbot/werkbot-search/sass/search';`\
... or include `'vendor/werkbot'` in your [build path](https://webpack.js.org/loaders/sass-loader/#object-1): `includePaths: [ 'vendor/werkbot' ]`\
and import the style like this: `@import 'werkbot-search/sass/search';`

**JavaScript**
- Add an [alias to your build](https://webpack.js.org/configuration/resolve/#resolvealias): `'werkbot-search-modal': './vendor/werkbot/werkbot-search/js/search-modal.js'`
- Import js: `require('werkbot-search-modal');`
- Insert the control to open the search modal in your template: `<% include SearchModalLink %>`\
By default, this is a list item (`li`) with a Font Awesome search icon.
- Add the modal to your Page.ss: `<% include SearchModal %>`
