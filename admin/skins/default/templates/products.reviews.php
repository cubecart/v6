{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2017. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {if $LIST_REVIEWS}
   <div id="reviews" class="tab_content">
      <h3>{$LANG.reviews.title_reviews}</h3>
      {if $REVIEWS}
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
         <a href="?_g=products&amp;node=reviews">{$LANG.common.reset}</a>
      </div>
      {foreach from=$REVIEWS item=review}
      <div class="note">
         <span class="actions">
         <input type="hidden" class="toggle" name="approve[{$review.id}]" id="approve_{$review.id}" value="{$review.approved}">
         <a href="{$review.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
         <a href="{$review.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
         </span>
         <div>
            <input type="checkbox" class="all-reviews" id="multi_{$review.id}" name="delete[individual][{$review.id}]" value="" /><strong>{$review.title}</strong>
         </div>
         <p>{$review.review}</p>
         <div class="details">
            <span style="float: right;">
            {section name=i start=1 loop=6 step=1}<input type="radio" class="rating" name="rating_{$review.id}" value="{$smarty.section.i.index}" disabled="disabled" {if $review.rating == $smarty.section.i.index}checked="checked"{/if}>{/section}
            </span>
            <a href="index.php?_a=product&amp;product_id={$review.product_id}" target="_blank">{$review.product.name}</a> &raquo;
            {$review.date} - {$review.name} &lt;<a href="mailto:{$review.email}">{$review.email}</a>&gt; {$review.ip_address}
         </div>
      </div>
      {/foreach}
      <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/select_all.gif" alt="">
      <a href="#" class="check-all" rel="all-reviews">{$LANG.form.check_uncheck}</a>
      <br>
      {$LANG.orders.with_selected}:
      <select name="multi-status" class="textbox">
         <option value="delete">{$LANG.common.delete}</option>
      </select>
      <input type="submit" value="{$LANG.common.go}" name="go" class="tiny submit_confirm" title="{$LANG.notification.confirm_delete}">
      <div class="pagination">
         <span>{$LANG.common.total}: {$TOTAL_RESULTS}</span>{$PAGINATION}
      </div>
      {else}
      <p>{$LANG.reviews.error_reviews_none}</p>
      {/if}
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
      <h3>{if $FORM_MODE=='edit'}{$LANG.reviews.title_review_edit}{else}{$LANG.catalogue.add_review}{/if}</h3>
      <fieldset>
         {if $FORM_MODE=='add'}
         <div><label for="ajax_name">{$LANG.common.product}</label><span><input type="hidden" id="ajax_product_id" name="review[product_id]" rel="product_id"><input type="text" id="ajax_name" placeholder="{$LANG.common.type_to_search}" class="textbox ajax not-empty" rel="product"></span></div>
         {/if}
         <div><label for="review_approved">{$LANG.common.status}</label><span><input type="hidden" class="toggle" name="review[approved]" id="review_approved" value="{$REVIEW.approved}"></span></div>
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
      </fieldset>
   </div>
   {/if}
   {include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
      <input type="submit" value="{$LANG.form.submit}" class="submit">
   </div>
   
</form>