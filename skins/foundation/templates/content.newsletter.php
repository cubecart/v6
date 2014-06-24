{if isset($CTRL_VIEW) && $CTRL_VIEW}
<h2>{$NEWSLETTER.subject}</h2>
<div>{$NEWSLETTER.content_html}</div>
{else}
<h2>{$LANG.newsletter.subscription}</h2>
{if $IS_USER}
{if $SUBSCRIBED}
<p>{$LANG.newsletter.customer_is_subscribed}</p>
<a href="{$URL.unsubscribe}" class="button alert">{$LANG.newsletter.unsubscribe}</a>
{else}
<p>{$LANG.newsletter.customer_not_subscribed}</p>
<a href="{$URL.subscribe}" class="button">{$LANG.newsletter.subscribe_now}</a>
{/if}
{else}
<p>{$LANG.newsletter.enter_email_subscribe_unsubscribe}</p>
<form action="{$VAL_SELF}" method="post" id="newsletter_form">
   <div class="row">
      <div class="small-12 large-8 columns">
         <label for="newsletter_email">{$LANG.common.email}</label>
         <input type="text" name="subscribe" class="required" id="newsletter_email" placeholder="{$LANG.common.email} {$LANG.form.required}">
      </div>
   </div>
   <div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
   <div class="hide" id="validate_already_subscribed">{$LANG.newsletter.notify_already_subscribed}</div>
   <div class="row">
      <div class="small-12 large-8 columns"><input name="submit" class="button" type="submit" value="{$LANG.form.submit}"></div>
   </div>
</form>
{/if}
<h2>{$LANG.newsletter.newsletters}</h2>
{if isset($NEWSLETTERS)}
<p>{$LANG.newsletter.view_newsletter_archive}</p>
<table>
   <thead>
      <tr>
         <th>{$LANG.common.subject}</th>
         <th>{$LANG.common.date}</th>
      </tr>
   </thead>
   <tbody>
      {foreach from=$NEWSLETTERS item=newsletter}
      <tr>
         <td><a href="{$newsletter.view}">{$newsletter.subject}</a></td>
         <td>{$newsletter.date_sent}</td>
      </tr>
      {/foreach}
   </tbody>
</table>
{else}
<p>{$LANG.newsletter.no_archived_newsletters}</p>
{/if}
{/if}
