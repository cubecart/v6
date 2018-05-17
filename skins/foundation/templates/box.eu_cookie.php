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
{if $COOKIE_DIALOGUE}
<div class="row" id="eu_cookie_dialogue">
   <form action="{$VAL_SELF}" class="marg" method="POST">
      <div class="small-9 columns">
      {assign "find" array('%s', '%PRIVACY_URL%')}
      {assign "replace" array({$CONFIG.store_name}, {$COOKIE_PRIVACY_LINK})}
      {$LANG.notification.cookie_dialogue|replace:$find:$replace}
      </div>
      <div class="small-3 columns">
        <ul class="button-group right">
          <li><input type="submit" class="eu_cookie_button button tiny secondary" name="accept_cookies_submit" value="{$LANG.common.accept}"></li>
          <li><input type="submit" class="eu_cookie_button button tiny alert" name="decline_cookies_accept" value="{$LANG.common.decline}"></li> 
        </ul>
      </div>
   </form>
</div>
{/if}