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
   <div id="general" class="tab_content">
   {if $CONFIG.newsletter_status=='0'}
   <p>{$LANG.newsletter.disabled_notice}</p>
   {/if}
      <fieldset>
         <legend>{$LANG.email.title_overview}</legend>
         <div><label for="email_subject">{$LANG.email.news_subject}</label><span><input type="text" name="newsletter[subject]" id="email_subject" class="required textbox" value="{$NEWSLETTER.subject}"></span></div>
         <div><label for="sender_name">{$LANG.email.news_sender_name}</label><span><input type="text" name="newsletter[sender_name]" id="sender_name" class="textbox" value="{$NEWSLETTER.sender_name}"> ({$LANG.email.empty_equals_default})</span></div>
         <div><label for="sender_email">{$LANG.email.news_sender_email}</label><span><input type="text" name="newsletter[sender_email]" id="sender_email" class="textbox" value="{$NEWSLETTER.sender_email}"> ({$LANG.email.empty_equals_default})</span></div>
         <div>
            <label for="dbl_opt">{$LANG.email.news_dbl_opt_only}*</label>
            <span>
               <input type="hidden" id="dbl_opt" name="newsletter[dbl_opt]" class="toggle"
               {if isset($CONFIG.subscribe_mode_lax) && $CONFIG.subscribe_mode_lax=='1'}
               value="{if !isset($NEWSLETTER.dbl_opt)}{$CONFIG.dbl_opt}{else}{$NEWSLETTER.dbl_opt}{/if}">
               {else}
               value="1" style="pointer-events: none;" tabindex="-1">
               {/if}
            </span>
         </div>
         <div>
            <label for="template_id">{$LANG.email.email_template}</label>
            <span>
               <select name="newsletter[template_id]" id="template_id" class="textbox">
                  <option value="0">{$LANG.form.none}</option>
                  {if isset($EXISTING_TEMPLATES)}
                  {foreach from=$EXISTING_TEMPLATES item=template}
                  <option value="{$template.template_id}"{$template.selected}>{$template.title}</option>
                  {/foreach}
                  {/if}
               </select>
            </span>
         </div>
      </fieldset>
      * {$LANG.email.dbl_opt_strict}
   </div>
   <div id="email_html" class="tab_content">
      <h3>{$LANG.email.title_content_html}</h3>
      <p>{$LANG.email.help_content_html}</p>
      <textarea name="newsletter[content_html]" id="content_html" class="textbox fck">{$NEWSLETTER.content_html|escape:"html"}</textarea>
   </div>
   <div id="send_test" class="tab_content">
      <h3>{$LANG.email.title_send_test}</h3>
      <p>{$LANG.email.help_test_send}</p>
      <fieldset>
         <div><label for="email_test">{$LANG.email.test_email}</label><span><input type="text" name="newsletter[test_email]" id="email_test" class="textbox" value=""> <input type="submit" value="{$LANG.email.save_send_test}" class="tiny"></span></div>
      </fieldset>
   </div>
      {if isset($PLUGIN_TABS)}
   {foreach from=$PLUGIN_TABS item=tab}
   {$tab}
   {/foreach}
   {/if}
{include file='templates/element.hook_form_content.php'}
   <div class="form_control">
      <input type="hidden" name="newsletter[newsletter_id]" value="{$NEWSLETTER.newsletter_id}">
      <input type="hidden" name="previous-tab" id="previous-tab" value="">
      <input type="submit" value="{$LANG.common.save}">
   </div>
   
</form>
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
            <td>{$LANG.common.created}</td>
            <td>{$LANG.email.news_status}</td>
            <td></td>
         </tr>
      </thead>
      <tbody>
         {foreach from=$NEWSLETTERS item=newsletter}
         <tr>
            <td><a href="{$newsletter.edit}" class="edit">{$newsletter.subject}</a></td>
            <td>{$newsletter.date_created_formatted}</td>
            <td>{$newsletter.status_text}</td>
            <td><span class="actions">
               <a href="{$newsletter.edit}" class="edit" title="{$LANG.common.edit}"><i class="fa fa-pencil-square-o" title="{$LANG.common.edit}"></i></a>
               <a href="{$newsletter.delete}" class="delete" title="{$LANG.notification.confirm_delete}"><i class="fa fa-trash" title="{$LANG.common.delete}"></i></a>
               {if $newsletter.can_send}
               <a href="{$newsletter.send}" class="button tiny confirm" title="{$LANG.email.confirm_send}"><i class="fa fa-plane"></i> {$LANG.common.send}</a>
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
               </span>
            </td>
         </tr>
         {/foreach}
      </tbody>
   </table>
   {else}
   <p>{$LANG.email.news_none}</p>
   {/if}
</div>
{/if}