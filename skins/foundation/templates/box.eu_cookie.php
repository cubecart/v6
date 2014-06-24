{if $COOKIE_DIALOGUE}
<div class="row" id="eu_cookie_dialogue">
   <form action="{$VAL_SELF}" class="marg" method="POST">
      <div class="small-10 columns">
         {$LANG.notification.cookie_dialogue|replace:'%s':{$CONFIG.store_name}}
      </div>
      <div class="small-2 columns">
         <input type="submit" class="button tiny secondary" name="accept_cookies_submit" id="eu_cookie_button" value="{$LANG.common.continue}">
         <input type="hidden" name="accept_cookies" value="1">
      </div>
   </form>
</div>
{/if}