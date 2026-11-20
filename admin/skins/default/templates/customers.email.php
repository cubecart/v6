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
{if isset($DISPLAY_FORM)}
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   {* One page, laid out as an email's content is: the newsletter in the middle as it will be
      sent, what describes it beside it, and preview, test and save beneath (#4738) *}
   <div id="general" class="tab_content">
      <h3>{$NEWSLETTER_TITLE}</h3>
      {if $CONFIG.newsletter_status=='0'}
      <p>{$LANG.newsletter.disabled_notice}</p>
      {/if}
      <div class="email-editor">
         <div class="email-editor__main">
            {* A newsletter is an email: it goes through Smarty, and mail clients need images with a
               whole address and a size. It is edited the way the emails are (#4742). The template
               chosen beside it is drawn round the content, which is the only part that can be changed. *}
            <textarea name="newsletter[content_html]" id="content_html" class="textbox cc-editor" aria-label="{$LANG.email.title_content_html}" data-cc-smarty="1" data-cc-email="1" data-cc-height="680" data-cc-macros="{$NEWSLETTER_MACROS|escape:'html'}" data-cc-frame="#template_id" data-cc-frames="{$NEWSLETTER_FRAMES|escape:'html'}">{$NEWSLETTER.content_html|escape:"html"}</textarea>
            {if $NEWSLETTER_FRAMED}<p class="email-field-help">{$LANG.email.help_content_template}</p>{/if}
         </div>
         <div class="email-editor__side">
            <div class="email-editor__field">
               <label for="email_subject">{$LANG.email.news_subject}</label>
               <input type="text" name="newsletter[subject]" id="email_subject" class="required textbox" value="{$NEWSLETTER.subject}">
            </div>
            <div class="email-editor__field">
               <label for="email_preheader">{$LANG.email.preview_text}</label>
               <input type="text" name="newsletter[preheader]" id="email_preheader" class="textbox" maxlength="255" value="{$NEWSLETTER.preheader|escape:'html'}" aria-describedby="email_preheader_help">
               <span id="email_preheader_help" class="email-field-help">{$LANG.email.preview_text_help}</span>
            </div>
            <div class="email-editor__field">
               <label for="template_id">{$LANG.email.email_template}</label>
               <select name="newsletter[template_id]" id="template_id" class="textbox">
                  <option value="0">{$LANG.form.none}</option>
                  {if isset($EXISTING_TEMPLATES)}
                  {foreach from=$EXISTING_TEMPLATES item=template}
                  <option value="{$template.template_id}"{$template.selected}>{$template.title}</option>
                  {/foreach}
                  {/if}
               </select>
            </div>
            <div class="email-editor__field">
               <label for="sender_name">{$LANG.email.news_sender_name}</label>
               <input type="text" name="newsletter[sender_name]" id="sender_name" class="textbox" value="{$NEWSLETTER.sender_name}" aria-describedby="sender_name_help">
               <span id="sender_name_help" class="email-field-help">{$LANG.email.empty_equals_default}</span>
            </div>
            <div class="email-editor__field">
               <label for="sender_email">{$LANG.email.news_sender_email}</label>
               <input type="text" name="newsletter[sender_email]" id="sender_email" class="textbox" value="{$NEWSLETTER.sender_email}" aria-describedby="sender_email_help">
               <span id="sender_email_help" class="email-field-help">{$LANG.email.empty_equals_default}</span>
            </div>
            <div class="email-editor__field">
               <label for="name_fallback">{$LANG.email.news_name_fallback}</label>
               <input type="text" name="newsletter[name_fallback]" id="name_fallback" class="textbox" maxlength="64" value="{$NEWSLETTER.name_fallback|escape:'html'}" aria-describedby="name_fallback_help">
               <span id="name_fallback_help" class="email-field-help">{$LANG.email.news_name_fallback_help}</span>
            </div>
            <div class="email-editor__field">
               <label for="send_at">{$LANG.email.news_send_at}</label>
               <input type="datetime-local" name="newsletter[send_at]" id="send_at" class="textbox" min="{$NEWSLETTER_SEND_FROM}" value="{$NEWSLETTER_SEND_AT}" aria-describedby="send_at_help">
               <span id="send_at_help" class="email-field-help">{$LANG.email.news_send_at_help}</span>
            </div>
            <div class="email-editor__field email-editor__field--toggle">
               <label for="dbl_opt">{$LANG.email.news_dbl_opt_only}*</label>
               <input type="hidden" id="dbl_opt" name="newsletter[dbl_opt]" class="toggle"
               {if isset($CONFIG.subscribe_mode_lax) && $CONFIG.subscribe_mode_lax=='1'}
               value="{if !isset($NEWSLETTER.dbl_opt)}{$CONFIG.dbl_opt}{else}{$NEWSLETTER.dbl_opt}{/if}">
               {else}
               value="1" style="pointer-events: none;" tabindex="-1">
               {/if}
            </div>
         </div>
      </div>
      <div class="email-tools email-tools--form">
         {* Save comes first in the form, so that it is the button Enter presses, and last on the page *}
         <span class="email-tools__group email-tools__save"><input type="submit" value="{$LANG.common.save}"></span>
         <button type="button" class="button" id="preview_newsletter" onclick="previewNewsletter()">{$LANG.common.preview}</button>
         <span class="email-tools__group">
            <label for="email_test">{$LANG.email.title_send_test}</label>
            <input type="text" inputmode="email" autocomplete="email" name="newsletter[test_email]" id="email_test" class="textbox" value="{$NEWSLETTER_TEST_TO|escape:'html'}">
            <button type="submit" name="send_test" value="1" class="button" id="newsletter_send_test">{$LANG.email.save_send_test}</button>
         </span>
      </div>
      <p class="email-field-help">* {$LANG.email.dbl_opt_strict}</p>
   </div>
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
   <input type="hidden" name="newsletter[newsletter_id]" value="{$NEWSLETTER.newsletter_id}">
   <input type="hidden" name="previous-tab" id="previous-tab" value="">

</form>
<script>{literal}
   // Enter in the test address sends the test, as its button does. Anywhere else it saves.
   (function () {
      var address = document.getElementById('email_test'), button = document.getElementById('newsletter_send_test');
      if (address && button) {
         address.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
               event.preventDefault();
               button.click();
            }
         });
      }
   })();
{/literal}</script>
{/if}
{if isset($DISPLAY_LIST)}
{if $NEWSLETTER_AUTO_REFRESH}
<meta http-equiv="refresh" content="15">
{/if}
<div id="newsletter-list" class="tab_content">
<h3>{$LANG.email.title_newsletters}</h3>
{if isset($THROTTLE_BANNER)}
<p class="notice {if $THROTTLE_BANNER.throttled}error{/if}">
   {$THROTTLE_BANNER.status_text} &middot; {$THROTTLE_BANNER.next_batch_text}{if $THROTTLE_BANNER.window_resets_text} &middot; {$THROTTLE_BANNER.window_resets_text}{/if}
</p>
<p class="notice"><i class="fa fa-info-circle"></i> {$LANG.email.background_send_notice}</p>
{/if}
   {if $CONFIG.newsletter_status=='0'}
   <p>{$LANG.newsletter.disabled_notice}</p>
   {/if}
   {if isset($NEWSLETTERS)}
   <table width="100%">
      <thead>
         <tr>
            <td>{$LANG.email.news_subject}</td>
            <td>{$LANG.email.news_sender_name}</td>
            <td>{$LANG.email.news_sender_email}</td>
            <td class="text-center" nowrap="nowrap">{$LANG.email.news_dbl_opt_only}</td>
            <td>{$LANG.email.email_template}</td>
            <td>{$LANG.common.created}</td>
            <td>{$LANG.email.news_status}</td>
            <td>{$LANG.email.news_results}</td>
            <td></td>
            <td></td>
         </tr>
      </thead>
      <tbody>
         {foreach from=$NEWSLETTERS item=newsletter}
         <tr>
            <td>{if $newsletter.can_edit}<a href="{$newsletter.edit}" class="edit">{$newsletter.subject}</a>{else}{$newsletter.subject}{/if}</td>
            <td>{$newsletter.sender_name}</td>
            <td>{$newsletter.sender_email}</td>
            <td class="text-center">{if $newsletter.dbl_opt}<i class="fa fa-check" style="color:#28a745"></i>{else}<i class="fa fa-times" style="color:#dc3545"></i>{/if}</td>
            <td>{$newsletter.template_title|default:'&mdash;' nofilter}</td>
            <td>{$newsletter.date_created_formatted}</td>
            <td>{$newsletter.status_text}</td>
            <td>{$newsletter.results}</td>
            <td>
               <span class="actions">
               {if $newsletter.can_edit}
               <a href="{$newsletter.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
               {/if}   
               <a href="#" class="preview" title="{$LANG.common.preview}" onclick="previewNewsletterRow({$newsletter.newsletter_id});return false;"><i class="fa fa-search" title="{$LANG.common.preview}"></i></a>
               {if $NEWSLETTER_CAN_EDIT}
               <a href="{$newsletter.copy}" class="copy" title="{$LANG.common.copy}"><i class="fa fa-files-o" title="{$LANG.common.copy}"></i></a>
               {/if}
               {if $newsletter.can_delete}
               <a href="{$newsletter.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
               {/if}
               </span>
            </td>
            <td class="text-center">
               {if $newsletter.can_send}
               <a href="{$newsletter.send}" class="button tiny confirm" title="{$newsletter.send_confirm}"><i class="fa fa-plane"></i> {$newsletter.send_label}</a>
               {/if}
               {if $newsletter.can_unschedule}
               <a href="{$newsletter.unschedule}" class="button tiny">{$LANG.email.news_unschedule}</a>
               {/if}
               {if $newsletter.can_pause}
               <a href="{$newsletter.pause}" class="button tiny confirm" title="{$LANG.email.confirm_pause}">{$LANG.common.pause}</a>
               {/if}
               {if $newsletter.can_resume}
               <a href="{$newsletter.resume}" class="button tiny confirm" title="{$LANG.email.confirm_resume}">{$LANG.common.resume}</a>
               {/if}
               {if $newsletter.can_cancel}
               <a href="{$newsletter.cancel}" class="button tiny delete confirm" title="{$LANG.email.confirm_cancel}">{$LANG.common.cancel}</a>
               {/if}
            </td>
         </tr>
         {/foreach}
      </tbody>
   </table>
   <p>{$LANG.email.news_results_help}</p>
   {else}
   <p>{$LANG.email.news_none}</p>
   {/if}
</div>
{/if}

{if isset($NEWSLETTER_TEMPLATES_JSON)}
<script>var NEWSLETTER_TEMPLATES = {$NEWSLETTER_TEMPLATES_JSON nofilter};</script>
{else}
<script>var NEWSLETTER_TEMPLATES = {ldelim}{rdelim};</script>
{/if}
{if isset($PREVIEW_MACROS_JSON)}
<script>var EMAIL_PREVIEW_MACROS = {$PREVIEW_MACROS_JSON nofilter};</script>
{else}
<script>var EMAIL_PREVIEW_MACROS = {ldelim}{rdelim};</script>
{/if}
{if isset($NEWSLETTERS_PREVIEW_JSON)}
<script>var NEWSLETTERS_PREVIEW = {$NEWSLETTERS_PREVIEW_JSON nofilter};</script>
{else}
<script>var NEWSLETTERS_PREVIEW = {ldelim}{rdelim};</script>
{/if}
<script>{literal}
   // Shared renderer — wraps body in the chosen template, substitutes
   // {$DATA.X} macros, and opens the result in a Colorbox iframe.
   function _renderNewsletterPreview(content, templateId, subject) {
      var wrapper = (NEWSLETTER_TEMPLATES && NEWSLETTER_TEMPLATES[templateId]) ? NEWSLETTER_TEMPLATES[templateId] : '';
      var rendered = wrapper ? wrapper.replace(/\{\$EMAIL_CONTENT\}/g, content) : content;
      Object.keys(EMAIL_PREVIEW_MACROS).forEach(function(key) {
         var pattern = new RegExp('\\{\\$DATA\\.' + key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\}', 'g');
         rendered = rendered.replace(pattern, EMAIL_PREVIEW_MACROS[key]);
      });
      $.colorbox({
         title: subject || '',
         width: '90%',
         height: '90%',
         html: function(){
            return '<iframe width="100%" height="95%" frameBorder="0" srcdoc="<div style=&quot;margin:auto;width:50%;&quot;>'+rendered.replace(/&/g,'&amp;').replace(/"/g,'&quot;')+'</div>"></iframe>';
         }
      });
   }

   // Editor button — pulls live data from the form fields.
   function previewNewsletter() {
      var box = document.getElementById('content_html'), field = (window.ccEditor && box) ? ccEditor.of(box) : null;
      var content = field ? field.source() : ((box || {}).value || '');
      var tplId   = parseInt((document.getElementById('template_id') || {}).value || 0, 10);
      var subject = (document.getElementById('email_subject') || {}).value || '';
      _renderNewsletterPreview(content, tplId, subject);
   }

   // List magnifier — pulls saved data from the per-row map.
   function previewNewsletterRow(id) {
      var row = NEWSLETTERS_PREVIEW && NEWSLETTERS_PREVIEW[id];
      if (!row) return;
      _renderNewsletterPreview(row.content_html || '', parseInt(row.template_id || 0, 10), row.subject || '');
   }
{/literal}</script>
<hr>
<p><small>Newsletters are sent in batches by a background task. Enable the &quot;Process Newsletter Queue&quot; <a href="?_g=settings#Scheduled_Tasks">scheduled task</a> and make sure a cron job is set up to trigger it. To protect your sending reputation, sending is paced out — and the hourly cap counts all outgoing email (including order confirmations and other transactional messages), not just newsletters. See <a href="https://kb.cubecart.com/administration/admin-panel/how-cubecart-throttles-newsletter-email-sending" target="_blank">how email sending is throttled</a> for what to expect and how to adjust the limits.</small></p>