{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" class="ignore-dirty">
  <div id="general" class="tab_content">
	<p><a href="{$MERGE_BACK}"><i class="fa fa-arrow-left"></i> {$MERGE_KEEP.name}</a></p>
	<h3>{$LANG.customer.merge_title}</h3>

	{if isset($MERGE_FROM)}
	{* Review: both accounts side by side, then what merging would do *}
	<table width="100%" class="merge-accounts">
	  <thead>
		<tr>
		  <td width="20%">&nbsp;</td>
		  <td width="40%">{$LANG.customer.merge_keep}</td>
		  <td width="40%">{$LANG.customer.merge_from}</td>
		</tr>
	  </thead>
	  <tbody>
		<tr>
		  <td><strong>{$LANG.common.name}</strong></td>
		  <td><a href="{$MERGE_KEEP.edit}">{$MERGE_KEEP.name}</a></td>
		  <td><a href="{$MERGE_FROM.edit}">{$MERGE_FROM.name}</a></td>
		</tr>
		<tr>
		  <td><strong>{$LANG.common.email}</strong></td>
		  <td>{$MERGE_KEEP.email}</td>
		  <td>{$MERGE_FROM.email}</td>
		</tr>
		<tr>
		  <td><strong>{$LANG.customer.customer_type}</strong></td>
		  <td><i class="fa fa-user {if $MERGE_KEEP.type==2}unregistered{else}registered{/if}"></i> {$MERGE_KEEP.type_name}</td>
		  <td><i class="fa fa-user {if $MERGE_FROM.type==2}unregistered{else}registered{/if}"></i> {$MERGE_FROM.type_name}</td>
		</tr>
		<tr>
		  <td><strong>{$LANG.customer.date_registered}</strong></td>
		  <td>{$MERGE_KEEP.registered}</td>
		  <td>{$MERGE_FROM.registered}</td>
		</tr>
		<tr>
		  <td><strong>{$LANG.customer.order_count}</strong></td>
		  <td>{$MERGE_KEEP.orders}</td>
		  <td>{$MERGE_FROM.orders}</td>
		</tr>
	  </tbody>
	</table>

	{if !empty($MERGE_PROBLEM)}
	<p class="important"><i class="fa fa-exclamation-triangle"></i> <strong>{$MERGE_PROBLEM}</strong></p>
	<p><a href="{$MERGE_SWAP.url}" class="button">{$MERGE_SWAP.label}</a></p>
	{else}
	<fieldset><legend>{$LANG.customer.merge_what}</legend>
	  {if !empty($MERGE_ROWS)}
	  {foreach from=$MERGE_ROWS item=row}
	  <div><label>{$row.label}</label><span>{$row.value}</span></div>
	  {/foreach}
	  {else}
	  <p>{$LANG.customer.merge_empty}</p>
	  {/if}
	</fieldset>
	<p class="important"><i class="fa fa-exclamation-triangle"></i> <strong>{$MERGE_WARNING}</strong></p>
	<input type="hidden" name="merge[from]" value="{$MERGE_FROM.customer_id}">
	<input type="hidden" name="merge[confirm]" value="1">
	{/if}

	{else}
	{* Choose the account to merge in *}
	<p>{$LANG.customer.merge_intro}</p>
	<fieldset><legend>{$LANG.customer.merge_keep}</legend>
	  <div><label>{$LANG.common.name}</label><span><a href="{$MERGE_KEEP.edit}">{$MERGE_KEEP.name}</a></span></div>
	  <div><label>{$LANG.common.email}</label><span>{$MERGE_KEEP.email}</span></div>
	  <div><label>{$LANG.customer.customer_type}</label><span><i class="fa fa-user {if $MERGE_KEEP.type==2}unregistered{else}registered{/if}"></i> {$MERGE_KEEP.type_name}</span></div>
	  <div><label>{$LANG.customer.order_count}</label><span>{$MERGE_KEEP.orders}</span></div>
	</fieldset>

	<fieldset><legend>{$LANG.customer.merge_likely}</legend>
	  {if !empty($MERGE_CANDIDATES)}
	  <table width="100%">
		<thead>
		  <tr>
			<td width="32">&nbsp;</td>
			<td>{$LANG.common.name}</td>
			<td>{$LANG.common.email}</td>
			<td>{$LANG.customer.customer_type}</td>
			<td>{$LANG.customer.date_registered}</td>
			<td style="text-align:center">{$LANG.customer.order_count}</td>
			<td>{$LANG.customer.merge_match}</td>
		  </tr>
		</thead>
		<tbody>
		  {foreach from=$MERGE_CANDIDATES item=candidate}
		  <tr>
			<td align="center"><input type="radio" name="merge[pick]" id="merge_pick_{$candidate.customer_id}" value="{$candidate.customer_id}"></td>
			<td><label for="merge_pick_{$candidate.customer_id}">{$candidate.name}</label></td>
			<td>{$candidate.email}</td>
			<td><i class="fa fa-user {if $candidate.type==2}unregistered{else}registered{/if}"></i> {$candidate.type_name}</td>
			<td>{$candidate.registered}</td>
			<td style="text-align:center">{$candidate.orders}</td>
			<td>{$candidate.reasons}</td>
		  </tr>
		  {/foreach}
		</tbody>
	  </table>
	  {else}
	  <p>{$LANG.customer.merge_likely_none}</p>
	  {/if}
	</fieldset>

	<fieldset><legend>{$LANG.customer.merge_find}</legend>
	  <div><label for="merge_search">{$LANG.common.name} / {$LANG.common.email}</label><span><input type="text" id="merge_search" class="textbox ajax" rel="user" placeholder="{$LANG.common.type_to_search}" autocomplete="off"><input type="hidden" id="result_merge_search" class="clickSubmit" name="merge[search]" value=""></span></div>
	</fieldset>
	{/if}
  </div>

  {include file='templates/element.hook_form_content.php'}

  <div class="form_control">
	{if isset($MERGE_FROM)}
	{if empty($MERGE_PROBLEM)}
	<input type="submit" name="save" value="{$LANG.customer.merge_title}">
	<a href="{$MERGE_SWAP.url}" class="button">{$MERGE_SWAP.label}</a>
	{/if}
	<a href="{$MERGE_URL}" class="button">{$LANG.common.cancel}</a>
	{else}
	<input type="submit" name="save" value="{$LANG.customer.merge_review}">
	<a href="{$MERGE_BACK}" class="button">{$LANG.common.cancel}</a>
	{/if}
  </div>
</form>
