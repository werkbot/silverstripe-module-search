<div class="content typography-space">
  <section class="clm-section">
    <div class="container flex-container">
      <div class="result desktop-100">
        <div class="space">
          <div class="search-query">
            <h2>You Searched for: <em>$Query</em></h2>
          </div>
          <% if $Results %>
            <% loop $Results %>
              <div class="search-result">
                <% if $getSearchableTitle %>
                  <h3>$getSearchableTitle</h3>
                <% else_if $Title %>
                  <h3>$Title</h3>
                <% end_if %>
                <p>
                  <% if $getSearchableSummary %>
                    $getSearchableSummary.ContextSummary(200)
                    <br />
                  <% end_if %>
                  <a href="$Link" class="button minor" aria-label="Read more about <% if $SearchableTitle %>$SearchableTitle<% else_if $Title %>$Title<% end_if %>">Read More</a>
                </p>
              </div>
            <% end_loop %>
          <% else %>
            <h3>Nothing Found</h3>
            <p>Sorry, your search query did not return any results.</p>
          <% end_if %>
        </div>
      </div>
      <% if $Results.MoreThanOnePage %>
        <div id="PageNumbers">
          <p>
            <% if $Results.NotFirstPage %>
              <a class="prev" href="$Results.PrevLink" title="View the previous page">Prev</a>
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
              <a class="next" href="$Results.NextLink" title="View the next page">Next</a>
            <% end_if %>
          </p>
        </div>
      <% end_if %>
    </div>
  </section>
</div>
