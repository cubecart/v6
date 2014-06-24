<form action="{$VAL_SELF}" method="post">
  <div id="general" class="tab_content">
	<h3>{$LANG.contact.title_contact}</h3>
	<p>{$LANG.contact.contact_info}</p>
	<fieldset><legend>{$LANG.contact.title_configuration}</legend>
	  <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="contact[status]" id="status" value="{$CONTACT.status}" class="toggle"></span></div>
	  <div><label for="email">{$LANG.contact.email_override}</label><span><input type="text" name="contact[email]" id="email" value="{$CONTACT.email}" class="textbox"></span></div>
	</fieldset>

	<fieldset><legend>{$LANG.contact.title_departments}</legend>
	  <div id="departments" class="list">
	  {if isset($DEPARTMENTS)}
	  {foreach from=$DEPARTMENTS item=department}
		<div>
		  {$LANG.common.name}: <input type="text" name="department[name][]" id="" value="{$department.name}" class="textbox">
		  {$LANG.common.email}: <input type="text" name="department[email][]" id="" value="{$department.email}" class="textbox">
		  <a href="#" class="remove" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
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
	<textarea name="contact[description]" id="description" class="textbox fck">{$CONTACT.description}</textarea>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>