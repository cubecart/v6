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
{if $CONFIG.exit_modal}
<div id="newsletter_exit" class="reveal-modal medium" data-reveal aria-labelledby="modalTitle" aria-hidden="true" role="dialog">
   <h2 id="modalTitle">{$LANG.email.exit_title}</h2>
   <p>{$LANG.email.exit_copy}</p>
   <form action="{$VAL_SELF}" method="post" id="newsletter_exit">
      <div class="hide">{$LANG.newsletter.enter_email_signup}</div>
      <div class="row collapse">
         <div class="small-8 columns"><input name="subscribe" id="newsletter_email_exit" type="text" size="18" maxlength="250" title="{$LANG.newsletter.subscribe}" placeholder="{$LANG.common.eg} joe@example.com"/></div>
         <div class="small-4 columns">
            <input type="submit" class="button postfix g-recaptcha" id="subscribe_button_exit" value="{$LANG.newsletter.subscribe}">
            <input type="hidden" name="force_unsubscribe" id="force_unsubscribe_exit" value="0">
         </div>
      </div>
      <div class="hide" id="newsletter_recaptcha">
         {include file='templates/content.recaptcha.php' ga_fid="newsletter_exit"}
      </div>
   </form>
   <div class="hide" id="validate_email_exit">{$LANG.common.error_email_invalid}</div>
   <div class="hide" id="validate_already_subscribed_exit">{$LANG.newsletter.notify_already_subscribed} {$LANG.newsletter.continue_to_unsubscribe}</div>
   <div class="hide" id="validate_subscribe_exit">{$LANG.newsletter.subscribe}</div>
   <div class="hide" id="validate_unsubscribe_exit">{$LANG.newsletter.unsubscribe}</div>
   <a class="close-reveal-modal" aria-label="Close">&#215;</a>
</div>
<script>
   function addEvent(obj, evt, fn) {
      if (obj.addEventListener) {
         obj.addEventListener(evt, fn, false);
      }
      else if (obj.attachEvent) {
         obj.attachEvent("on" + evt, fn);
      }
   }
   addEvent(window,"load",function(e) {
      addEvent(document, "mouseout", function(e) {
         e = e ? e : window.event;
         var from = e.relatedTarget || e.toElement;
         if (!from || from.nodeName == "HTML") {
               if(!$.cookie('newsletter_exit')) {
                  $('#newsletter_exit').foundation('reveal', 'open');
                  $.cookie('newsletter_exit', true, { expires: 30, path: '/' });
               }
         }
      });
   });
</script>
{/if}