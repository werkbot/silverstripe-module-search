<div class="search-query">
  <h2><%t Search.SEARCH_QUERY 'You Searched for:' %> <em>$Query</em></h2>
</div>
<% if $Results %>
  <% loop $Results %>
    <div class="search-result">
      <% if $getSearchableTitle %>
        <h3 class="search-result-title">$getSearchableTitle</h3>
      <% else_if $Title %>
        <h3>$Title</h3>
      <% end_if %>
      <p>
        <% if $getSearchableSummary %>
          $getSearchableSummary.ContextSummary(200)
          <br />
        <% end_if %>
        <a href="$Link" aria-label="Read more about <% if $getSearchableTitle %>$getSearchableTitle<% else_if $Title %>$Title<% end_if %>"><%t Search.BUTTON_READMORE 'Read More' %></a>
      </p>
    </div>
  <% end_loop %>
<% else %>
  <h3><%t Search.RESULTS_NOTFOUNDTITLE 'Nothing Found' %></h3>
  <p><%t Search.RESULTS_NOTFOUNDCONTENT 'Sorry, your search query did not return any results.' %></p>
<% end_if %>
<% if $Results.MoreThanOnePage %>
<div class="pagination">
  <p>
    <% if $Results.NotFirstPage %>
      <a class="pagination-prev" href="$Results.PrevLink" title="View the previous page">Prev</a>
    <% end_if %>
    <span>
      <% loop $Results.PaginationSummary(4) %>
        <% if $CurrentBool %>
          $PageNum
        <% else %>
          <% if $Link %>
            <a href="$Link" title="View page number $PageNum">$PageNum</a>
          <% else %>
            &hellip;
          <% end_if %>
        <% end_if %>
      <% end_loop %>
    </span>
    <% if $Results.NotLastPage %>
      <a class="pagination-next" href="$Results.NextLink" title="View the next page">Next</a>
    <% end_if %>
  </p>
</div>
<% end_if %>
