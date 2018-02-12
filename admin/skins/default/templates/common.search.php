<input type="text" class="form-control search" id="search-placeholder" placeholder="&#xF002; Search&hellip;">
<div id="sidebar_contain">
	<div id="sidebar_content">
	  <div class="sidebar_content">
		<form action="?_g=customers" method="post">
		  <h4>{$LANG.search.title_search_customers}</h4>
		  <input type="text" name="search[keywords]" placeholder="&#xF002; {$LANG.common.type_to_search}" id="customer_id" class="textbox left ajax" rel="user">
		  <input type="hidden" id="result_customer_id" class="clickSubmit" name="search[customer_id]" value="">
		  <input type="submit" value="{$LANG.common.go}" class="go_search">
		  
		</form>
	  </div>
	  <div class="sidebar_content">
		<form action="?_g=products" method="post">
		  <h4>{$LANG.search.title_search_products}</h4>
		  <input type="text" name="search[product]" placeholder="&#xF002; {$LANG.common.type_to_search}" id="product" class="textbox left ajax" rel="product">
		  <input type="submit" value="{$LANG.common.go}" class="go_search">
		   <input type="hidden" id="result_product" class="clickSubmit" name="search[product_id]" value="">
		   
		</form>
	  </div>
	  <div class="sidebar_content">
		<form action="?_g=orders" method="post">
		  <h4>{$LANG.search.title_search_orders}</h4>
		  <input type="text" name="search[order_number]" id="search_order" class="textbox left">
		  <input type="submit" value="{$LANG.common.go}" class="go_search">
		  
		</form>
	  </div>
	  {foreach from=$SIDEBAR_CONTENT item=content}<div class="sidebar_content">{$content}</div>{/foreach}
	</div>
</div>