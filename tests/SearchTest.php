<?php
/**/
namespace Werkbot\Search;
/**/
use Page;
use PageController;
use SilverStripe\CMS\Search\SearchForm;
use SilverStripe\Dev\CSSContentParser;
use SilverStripe\Dev\FunctionalTest;
/*
    Run with:
    vendor/bin/phpunit vendor/werkbot/werkbot-search/tests/SearchTest.php
*/
class SearchTest extends FunctionalTest
{
    protected static $fixture_file = 'Fixtures/SearchTest.yml';

    public function testSiteSearchForm()
    {
        // Create mock form object to pass into process method
        $mockForm = $this->createMock(SearchForm::class);
        $mockForm->expects($this->any())
            ->method('setSessionValidationResult')
            ->willReturn($mockForm);
        $mockForm->expects($this->once())
            ->method('getSearchQuery')
            ->willReturn('You searched for Test Result Page');

        // Create configured page controller
        $SearchController = PageController::create(Page::create());
        $SearchResultsPage = $SearchController->SiteSearchFormResults([
            'Search' => 'Test Result Page',
        ], $mockForm);

        // Generate expected page results
        $expectedResults = [
          'TestResultPageOne' => 'Test Result Page One',
          'TestResultPageTwo' => 'Test Result Page Two',
          'TestResultPageThree' => 'Test Result Page Three',
        ];
        $generatedPages = [];
        foreach ($expectedResults as $fixture => $page) {
            $obj = $this->objFromFixture(Page::class, $fixture);
            $obj->publishRecursive();
            array_push($generatedPages, $obj);
        }

        // Re-index search results
        (new SearchIndex())->run($SearchController->getRequest());

        // Confirm search results show
        foreach ($generatedPages as $page) {
            $items = CSSContentParser::create($SearchResultsPage)->getBySelector('.search-result-title');
            $actuals = [];
            if ($items) {
                foreach ($items as $item) {
                    $actuals[trim(preg_replace('/\s+/', ' ', (string)$item))] = true;
                }
            }
            $this->assertTrue(isset($actuals[$page->Title]), 'Expected result: "' . $page->Title . '" not found.');
            echo PHP_EOL . ' - Confirmed "' . $page->Title . '" shows in search results' . PHP_EOL;
        }
    }
}
