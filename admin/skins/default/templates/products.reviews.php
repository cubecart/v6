<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  {if $LIST_REVIEWS}
  <div id="reviews" class="tab_content">
	<h3>{$LANG.reviews.title_reviews}</h3>
	<div class="tools">
	  {$LANG.form.sort_by}
	  <select name="field" class="textbox">
		{foreach from=$FIELDS item=field}
		<option value="{$field.value}" {$field.selected}>{$field.name}</option>
		{/foreach}
	  </select>
	  <select name="sort" class="textbox">
		{foreach from=$SORTS item=sort}
		<option value="{$sort.value}" {$sort.selected}>{$sort.name}</option>
		{/foreach}
	  </select>
	  <input type="submit" class="submit mini_button" name="filter" value="{$LANG.common.go}">
	  <a href="?_g=products&node=reviews">{$LANG.common.reset}</a>
	</div>
	{foreach from=$REVIEWS item=review}
	<div class="note">
	  <span class="actions">
		<input type="hidden" class="toggle" name="approve[{$review.id}]" id="approve_{$review.id}" value="{$review.approved}">
		<a href="{$review.edit}" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		<a href="{$review.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
	  </span>
	  <div><strong>{$review.title}</strong></div>
	  <p>{$review.review}</p>
	  <div class="details">
		<span style="float: right;">
		  {section name=i start=1 loop=6 step=1}<input type="radio" class="rating" name="rating_{$review.id}" value="{$smarty.section.i.index}" disabled="disabled" {if $review.rating == $smarty.section.i.index}checked="checked"{/if}>{/section}
		</span>
		<a href="index.php?_a=product&product_id={$review.product_id}" target="_blank">{$review.product.name}</a> &raquo;
		{$review.date} - {$review.name} <<a href="mailto:{$review.email}">{$review.email}</a>> {$review.ip_address}
	  </div>
	</div>
	{foreachelse}
	<p>{$LANG.reviews.error_reviews_none}</p>
	{/foreach}

	<div class="pagination">
	  <span>{$LANG.common.total}: {$TOTAL_RESULTS}</span>{$PAGINATION}
	</div>
  </div>
  <div id="bulk_delete" class="tab_content">
	<h3>{$LANG.reviews.title_bulk_delete}</h3>
	<p>{$LANG.reviews.bulk_delete_desc}</p>
	<fieldset>
	  <div><label for="email">{$LANG.common.email}</label><span><input type="text" id="email" name="delete[email]" class="textbox"></span></div>
	  <div><label for="ip_address">{$LANG.common.ip_address}</label><span><input type="text" id="ip_address" name="delete[ip_address]" class="textbox"></span></div>
	</fieldset>
  </div>
  <div id="search" class="tab_content">
	<h3>{$LANG.reviews.title_review_search}</h3>
	<fieldset>
	  <div><label for="search-keywords">{$LANG.search.keywords}</label><span><input type="text" id="search-keywords" name="filter[keywords]" class="textbox"></span></div>
	  <div><label for="search-products">{$LANG.reviews.filter_by_product}</label>
	  	<span>
	  		<input type="hidden" name="filter[product_id]" id="ajax_product_id">
	  		<input type="text" name="filter[product_string]" id="search-products" class="textbox ajax" rel="product">
	  	</span>
	  </div>
	  <div>
		<label for="search-status">{$LANG.common.status}</label>
		<span>
		  <select name="filter[approved]">
			{foreach from=$STATUSES item=status}
			<option value="{$status.value}" {$status.selected}>{$status.name}</option>
			{/foreach}
		  </select>
		</span>
	  </div>
	</fieldset>
  </div>
  {/if}

  {if $DISPLAY_FORM}
  <div id="review" class="tab_content">
	<h3>{$LANG.reviews.title_review_edit}</h3>
	<div><label for="review_name">{$LANG.common.status}</label><span><input type="hidden" class="toggle" name="review[approved]" id="review_approved" value="{$REVIEW.approved}"></span></div>
	<div><label for="review_name">{$LANG.common.name}</label><span><input type="text" name="review[name]" id="review_name" value="{$REVIEW.name}" class="textbox"></span></div>
	<div><label for="review_email">{$LANG.common.email}</label><span><input type="text" name="review[email]" id="review_email" value="{$REVIEW.email}" class="textbox"></span></div>
	<div><label for="review_title">{$LANG.documents.document_title}</label><span><input type="text" name="review[title]" id="review_title" value="{$REVIEW.title}" class="textbox"></span></div>
	<div><label for="review_content">{$LANG.documents.document_content}</label><span><textarea name="review[review]" id="review_content" class="textbox">{$REVIEW.review}</textarea></span></div>
	<div><label for="">{$LANG.documents.rating}</label><span>
	{section name=i start=1 loop=6 step=1}<input type="radio" name="rating" value="{$smarty.section.i.index}" class="rating" {if $REVIEW.rating == $smarty.section.i.index}checked="checked"{/if}>{/section}
	&nbsp;</span>
	</div>
	<br>
	<input type="hidden" name="review[id]" value="{$REVIEW.id}">
  </div>
  {/if}
  
  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.form.submit}" class="submit">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>