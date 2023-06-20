<?php

namespace Werkbot\Search;

use SilverStripe\Core\Config\Configurable;
use Symbiote\QueuedJobs\Services\AbstractQueuedJob;
use Symbiote\QueuedJobs\Services\QueuedJobService;

/**
 * Remove Search Queries Job
 * Removes queries that are older then 30 days
 */
class RemoveSearchQueriesJob extends AbstractQueuedJob
{
  use Configurable;

  /**
   * @config
   */
  private static $max_age = '365 days';
  private static $queue_next_run = 'tomorrow';

  public function getTitle(): string
  {
      return 'Remove Search Queries Job';
  }

  public function setup(): void
  {
    $maxAge = $this->config()->get('max_age');
    $this->items = SearchQuery::get()->filter([
      "Created:LessThan" => date('Y-m-d H:i:s', strtotime('-' . $maxAge)),
    ])->sort('Created ASC')
      ->limit(100);
    $this->remaining = $this->items->toArray();
    $this->totalSteps = count($this->remaining);
  }

  public function process(): void
  {
    $remaining = $this->remaining;

    if (count($remaining) === 0) {
      $this->isComplete = true;
      return;
    }

    $item = array_shift($remaining);

    // Remove the query
    $this->addMessage("Removed query: " . $item->ID);
    $item->delete();

    // update job progress
    $this->remaining = $remaining;
    $this->currentStep += 1;

    // check for job completion
    if (count($remaining) === 0) {
      $this->isComplete = true;
    }
    return;
  }

  public function afterComplete()
  {
    $queueNextRun = $this->config()->get('queue_next_run');
    QueuedJobService::singleton()->queueJob(new RemoveSearchQueriesJob(), date('Y-m-d', strtotime($queueNextRun)));
  }
}
