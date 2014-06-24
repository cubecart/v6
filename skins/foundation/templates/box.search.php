<form action="{$STORE_URL}/search.html" id="search_form" method="get">
	<div class="row collapse">
		<div class="small-10 large-11 columns">
			<input name="search[keywords]" type="text" placeholder="{$LANG.search.input_default}" required>
		</div>
		<div class="small-2 large-1 columns">
			<button class="button postfix" type="submit" value="{$LANG.common.search}"><i class="fa fa-search"></i></button>
		</div>
	</div>
	<input type="hidden" name="_a" value="category">
</form>
<div class="hide" id="validate_search">{$LANG.search.enter_search_term}</div>