# Remove Search Queries Job
By default, the RemoveSearchQueriesJob removes SearchQueries that are more than a year old. This job also queues itself for tomorrow by default. This behavior can be altered in the RemoveSearchQueriesJob configuration.
```yml
Werkbot\Search\RemoveSearchQueriesJob:
  max_age: '30 days'
  queue_next_run: 'next week'
```
