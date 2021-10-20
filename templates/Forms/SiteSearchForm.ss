<form $FormAttributes>
  <% if $Message %>
	 <p id="{$FormName}_error" class="message $MessageType">$Message</p>
	<% else %>
	 <p id="{$FormName}_error" class="message $MessageType" style="display: none"></p>
	<% end_if %>
  <div class="flex-container">
    <div class="desktop-75">
  		<% loop $Fields %>
  		  $FieldHolder
  		<% end_loop %>
    </div>
    <div class="desktop-25">
      <% loop $Actions %>
        $Field
      <% end_loop %>
    </div>
  </div>
</form>
