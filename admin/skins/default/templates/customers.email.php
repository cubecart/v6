{if isset($DISPLAY_FORM)}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="general" class="tab_content">
	<fieldset><legend>{$LANG.email.title_overview}</legend>
	  <div><label for="email_subject">{$LANG.email.news_subject}</label><span><input type="text" name="newsletter[subject]" id="email_subject" class="textbox" value="{$NEWSLETTER.subject}"></span></div>
	  <div><label for="sender_name">{$LANG.email.news_sender_name}</label><span><input type="text" name="newsletter[sender_name]" id="sender_name" class="textbox" value="{$NEWSLETTER.sender_name}"> ({$LANG.email.empty_equals_default})</span></div>
	  <div><label for="sender_email">{$LANG.email.news_sender_email}</label><span><input type="text" name="newsletter[sender_email]" id="sender_email" class="textbox" value="{$NEWSLETTER.sender_email}"> ({$LANG.email.empty_equals_default})</span></div>
	  <div><label for="template_id">{$LANG.email.email_template}</label><span><select name="newsletter[template_id]" id="template_id" class="textbox">
	  <option value="0">{$LANG.form.none}</option>
	  {if isset($EXISTING_TEMPLATES)}
	  {foreach from=$EXISTING_TEMPLATES item=template}
	  <option value="{$template.template_id}"{$template.selected}>{$template.title}</option>
	  {/foreach}
	  {/if}
	  </select></span></div>
	</fieldset>
  </div>
  <div id="email_html" class="tab_content">
  <h3>{$LANG.email.title_content_html}</h3>
  <p>{$LANG.email.help_content_html}</p>
	<textarea name="newsletter[content_html]" id="content_html" class="textbox fck">{$NEWSLETTER.content_html}</textarea>
  </div>
  <div id="email_text" class="tab_content">
  <h3>{$LANG.email.title_content_text}</h3>
  <p>{$LANG.email.help_content_text}</p>
	<textarea name="newsletter[content_text]" id="content_text" class="textbox" style="width: 100%; height: 300px;">{$NEWSLETTER.content_text}</textarea>
  </div>
  <div id="send_test" class="tab_content">
	<h3>{$LANG.email.title_send_test}</h3>
	<p>{$LANG.email.help_test_send}</p>
	<fieldset>
	  <div><label for="email_test">{$LANG.email.test_email}</label><span><input type="text" name="newsletter[test_email]" id="email_test" class="textbox" value=""> <input type="submit" value="{$LANG.email.save_send_test}"></span></div>
	</fieldset>
  </div>
  
  {include file='templates/element.hook_form_content.php'}
  
  <div class="form_control">
	<input type="hidden" name="newsletter[newsletter_id]" value="{$NEWSLETTER.newsletter_id}">
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
	<input type="submit" value="{$LANG.common.save}">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>
{/if}

{if isset($DISPLAY_LIST)}
<div id="newsletter-list" class="tab_content">
  <h3>{$LANG.email.title_newsletters}</h3>
  {if isset($NEWSLETTERS)}
  <fieldset class="list">
  {foreach from=$NEWSLETTERS item=newsletter}
	<div>
	  <span class="actions">
		<a href="{$newsletter.send}" class="confirm" title="{$LANG.email.confirm_send}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/newspaper_go.png" alt="{$LANG.common.send}"></a>
		<a href="{$newsletter.edit}" class="edit" title="{$LANG.common.edit}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}"></a>
		<a href="{$newsletter.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}"></a>
	  </span>
	  <a href="{$newsletter.edit}" class="edit">{$newsletter.subject}</a>
	</div>
	{/foreach}
  </fieldset>
  {else}
  <p>{$LANG.email.news_none}</p>
  {/if}
</div>

<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data" target="_self">
  <div id="export_mailing_list" class="tab_content">
  <h3>{$LANG.email.title_export}</h3>
    <fieldset><legend>{$LANG.email.title_export_settings}</legend>
	<div><label for="format">{$LANG.email.export_format}</label>
	<span>
		<input type="text" name="maillist_format" id="format" class="textbox" value="{$EMAIL_ADDRESS}" title="{literal}e.g. {$EMAIL_ADDRESS} <{$FULL_NAME_SHORT}>{/literal}">
		<select name="maillist_extension">
			<option value="txt">.txt</option>
			<option value="csv">.csv</option>
		</select>
		<input type="submit" class="submit" id="mailing_list_export" value="{$LANG.common.export}">
		</span>
    </div>
	</fieldset>
	<table class="list">
	  <thead>
	    <tr>
	      <td>{$LANG.email.email_macro}</td>
	      <td>{$LANG.email.email_macro_available}</td>
	      <td>{$LANG.common.description}</td>
	    </tr>
	  </thead>
	  <tbody>
		<tr><td>{literal}{$EMAIL_ADDRESS}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/1.png" alt="{$LANG.common.yes}"></td><td>{$LANG.user.email_address} {$LANG.email.example_email}</td></tr>
	    <tr><td>{literal}{$FULL_NAME_LONG}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0.png" alt="{$LANG.common.no}"></td><td>{$LANG.user.fullname_long} {$LANG.email.example_fullname_long}</td></tr>
	    <tr><td>{literal}{$FULL_NAME_SHORT}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0.png" alt="{$LANG.common.no}"></td><td>{$LANG.user.fullname_short} {$LANG.email.example_fullname_short}</td></tr>
	    <tr><td>{literal}{$TITLE}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0.png" alt="{$LANG.common.no}"></td><td>{$LANG.user.title} {$LANG.email.example_title}</td></tr>
		<tr><td>{literal}{$FIRST_NAME}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0.png" alt="{$LANG.common.no}"></td><td>{$LANG.user.name_first} {$LANG.email.example_name_first}</td></tr>
		<tr><td>{literal}{$LAST_NAME}{/literal}</td><td align="center"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/0.png" alt="{$LANG.common.no}"></td><td>{$LANG.user.name_first} {$LANG.email.example_name_last}</td></tr>
	  </tbody>
	</table>
	<p>{$LANG.email.help_macro}</p>
	<input type="hidden" name="previous-tab" id="previous-tab" value="">
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}">
</form>
{/if}

{if isset($DISPLAY_SEND)}
<div class="tab_content" id="newsletter_send">
  <div id="progress_wrapper">
	<input type="hidden" id="newsletter_id" value="{$NEWSLETTER_ID}">
	<div id="progress_bar"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/loading.gif" alt="" class="newsletter"></div>
  </div>
  <div id="progress_bar_percent"></div>
  <p><a href="?_g=customers&node=email" class="delete" title="{$LANG.email.confirm_cancel}">{$LANG.email.news_cancel}</a></p>
</div>
{/if}