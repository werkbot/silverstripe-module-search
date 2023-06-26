# Remove Search Queries Job
The "Werkbot\Search\RemoveSearchQueriesJob" can be created in the CMS in the "Jobs" tab. The job can be executed immediately for testing, but a cron job must be setup in a live environment:
```sh
php /vendor/silverstripe/framework/cli-script.php dev/tasks/ProcessJobQueueTask
```

By default, the RemoveSearchQueriesJob removes SearchQueries that are more than a year old. This job also queues itself for tomorrow by default. This behavior can be altered in the RemoveSearchQueriesJob configuration.
```yml
Werkbot\Search\RemoveSearchQueriesJob:
  max_age: '30 days'
  queue_next_run: 'next week'
```
