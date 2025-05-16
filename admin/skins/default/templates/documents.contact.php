{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<form action="{$VAL_SELF}" method="post">
  <div id="general" class="tab_content">
	<h3>{$LANG.contact.title_contact}</h3>
	<p>{$LANG.contact.contact_info}</p>
	<fieldset><legend>{$LANG.contact.title_configuration}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="contact[status]" id="status" value="{$CONTACT.status}" class="toggle"></span></div>
	  <div><label for="email">{$LANG.contact.email_override}</label><span><input type="text" name="contact[email]" id="email" value="{$CONTACT.email}" class="textbox"></span></div>
	  <div><label for="phone">{$LANG.address.phone}</label>
	  	<span>
		  	<select name="contact[phone]">
			  <option value="0"{if $CONTACT.phone=='0'} selected="selected"{/if}>{$LANG.common.disabled}</option>
			  <option value="1"{if $CONTACT.phone=='1'} selected="selected"{/if}>{$LANG.common.enabled} {$LANG.common.optional}</option>
			  <option value="2"{if $CONTACT.phone=='2'} selected="selected"{/if}>{$LANG.common.enabled} ({$LANG.common.required})</option>
			</select>
		</span>
	  </div>
	  <div><label for="status">{$LANG.contact.allow_attachments} (Image, Zip, PDF)</label><span><input type="hidden" name="contact[attachments]" id="attachments" value="{$CONTACT.attachments}" class="toggle"></span></div>
	</fieldset>

	<fieldset><legend>{$LANG.contact.title_departments}</legend>
	  <div id="departments">
	  {if isset($DEPARTMENTS)}
	  {foreach from=$DEPARTMENTS item=department}
		<div>
		  {$LANG.common.name}: <input type="text" name="department[name][]" id="" value="{$department.name}" class="textbox">
		  {$LANG.common.email}: <input type="text" name="department[email][]" id="" value="{$department.email}" class="textbox">
		  <a href="#" class="remove" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
		</div>
	  {/foreach}
	  {/if}

	  </div>
	  <div class="list-footer">
			{$LANG.common.name}: <input type="text" name="department[name][]" id="" class="textbox">
			{$LANG.common.email}: <input type="text" name="department[email][]" id="" class="textbox"> {$LANG.common.optional}
	  </div>
	</fieldset>
  </div>
  <div id="pagecontent" class="tab_content">
  <h3>{$LANG.contact.title_content}</h3>
	<p>{$LANG.contact.content_info}</p>
	<textarea name="contact[description]" id="description" class="textbox fck">{$CONTACT.description|escape:"html"}</textarea>
	<div class="parse_content"><label for="cat_parse">{$LANG.catalogue.parse_content}</label><span><input type="hidden" id="cat_parse" name="cat[cat_parse]" value="{if !isset($CATEGORY.cat_parse)}0{else}{$CATEGORY.cat_parse}{/if}" class="toggle"></span></div>
  </div>
  <div id="seo" class="tab_content">
	<h3>{$LANG.settings.title_seo}</h3>
	<fieldset><legend>{$LANG.settings.title_seo_meta_data}</legend>
	  <div><label for="seo_meta_title">{$LANG.settings.seo_meta_title}</label><span><input type="text" name="contact[seo_meta_title]" id="seo_meta_title" value="{$CONTACT.seo_meta_title}" class="textbox strlen" rel="seo_meta_title_strlen"></span> <span id="seo_meta_title_strlen">{strlen($CONTACT.seo_meta_title|default:"")}</span></div>
	  <div><label for="seo_meta_description">{$LANG.settings.seo_meta_description}</label><span><textarea name="contact[seo_meta_description]" id="seo_meta_description" class="textbox strlen" rel="seo_meta_description_strlen">{$CONTACT.seo_meta_description}</textarea></span> <span id="seo_meta_description_strlen">{strlen($CONTACT.seo_meta_description|default:"")}</span></div>
	</fieldset>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  
</form>