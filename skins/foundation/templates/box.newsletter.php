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
{if !$DISABLE_BOX_NEWSLETTER}
<div id="box-newsletter">
   <h3>{$LANG.newsletter.mailing_list}</h3>
   {if $IS_USER}
   {if ($CTRL_SUBSCRIBED)}
   <p>{$LANG.newsletter.customer_is_subscribed}<br><a href="{$STORE_URL}/index.php?_a=newsletter&action=unsubscribe">{$LANG.newsletter.click_to_unsubscribe}</a></p>
   {else}
   <p>{$LANG.newsletter.customer_not_subscribed}<br><a href="{$STORE_URL}/index.php?_a=newsletter&action=subscribe">{$LANG.newsletter.click_to_subscribe}</a></p>
   {/if}
   {else}
   <form action="{$VAL_SELF}" method="post" id="newsletter_form_box">
      <div class="hide">{$LANG.newsletter.enter_email_signup}</div>
      <div class="row collapse">
         <div class="small-8 columns"><input name="subscribe" id="newsletter_email" type="text" size="18" maxlength="250" title="{$LANG.newsletter.subscribe}" placeholder="{$LANG.common.eg} joe@example.com"/></div>
         <div class="small-4 columns">
            <input type="submit" class="button postfix g-recaptcha" id="subscribe_button" value="{$LANG.newsletter.subscribe}">
            <input type="hidden" name="force_unsubscribe" id="force_unsubscribe" value="0">
         </div>
      </div>
      <div class="hide" id="newsletter_recaptcha">
      {include file='templates/content.recaptcha.php' ga_fid="Newsletter"}
      </div>
   </form>
   <div class="hide" id="validate_email">{$LANG.common.error_email_invalid}</div>
   <div class="hide" id="validate_already_subscribed">{$LANG.newsletter.notify_already_subscribed} {$LANG.newsletter.continue_to_unsubscribe}</div>
   <div class="hide" id="validate_subscribe">{$LANG.newsletter.subscribe}</div>
   <div class="hide" id="validate_unsubscribe">{$LANG.newsletter.unsubscribe}</div>
   {/if}
</div>
{/if}