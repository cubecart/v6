<input type="text" class="form-control search" id="search-placeholder" placeholder="&#xF002; Search">
  <div id="sidebar_contain">
		<div id="sidebar_content">
		  <div class="sidebar_content">
			<form action="?_g=customers" method="post">
			  <h4>{$LANG.search.title_search_customers}</h4>
			  <input type="text" name="search[keywords]" placeholder="&#xF002; {$LANG.common.type_to_search}" id="customer_id" class="textbox ajax" rel="user">
			  <input type="hidden" id="result_customer_id" name="search[customer_id]" value="">
			  <input type="submit" value="{$LANG.common.go}">
			  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
			</form>
		  </div>
		  <div class="sidebar_content">
			<form action="?_g=products" method="post">
			  <h4>{$LANG.search.title_search_products}</h4>
			  <input type="text" name="search[product]" placeholder="&#xF002; {$LANG.common.type_to_search}" id="product" class="textbox ajax" rel="product">
			  <input type="submit" value="{$LANG.common.go}">
			   <input type="hidden" id="result_product" name="search[product_id]" value="">
			   <input type="hidden" name="token" value="{$SESSION_TOKEN}">
			</form>
		  </div>
		  <div class="sidebar_content">
			<form action="?_g=orders" method="post">
			  <h4>{$LANG.search.title_search_orders}</h4>
			  <input type="text" name="search[order_number]" placeholder="&#xF002; {$LANG.common.type_to_search}" id="search_order" class="textbox"> <input type="submit" value="{$LANG.common.go}">
			  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
			</form>
		  </div>
		  {if isset($SIDEBAR_CONTENT)} {foreach from=$SIDEBAR_CONTENT item=content}<div class="sidebar_content">{$content}</div>{/foreach}{/if}
		</div>
	  </div>