{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if $COOKIE_SWITCH}
<div class="row">
   <div class="small-12 columns">
      <h2>{$LANG.notification.cookie_switch}</h2>
      <p>{$LANG.notification.cookie_switch_desc}</p>
      <ul class="button-group">
        <li><input type="button" class="eu_cookie_button button tiny secondary jsalert" name="accept_cookies_submit" value="{$LANG.common.accept}" data-alert-text="{$LANG.notification.cookies_accepted}"></li>
        <li><input type="button" class="eu_cookie_button button tiny alert jsalert" name="decline_cookies_accept" value="{$LANG.common.block}" data-alert-text="{$LANG.notification.cookies_blocked}"></li> 
      </ul>
    </div>
</div>

{/if}
{if {$DOCUMENT.hide_title==0}}
<div class="row">
   <div class="small-12 columns">
      <h2>{$DOCUMENT.doc_name}</h2>
   </div>
</div>
{/if}
<div class="row">
   <div class="small-12 columns">{$DOCUMENT.doc_content}</div>
</div>
{if $SHARE}
<hr>
<div class="row">
   <div class="small-12 columns">
      {foreach from=$SHARE item=html}
      {$html}
      {/foreach}
   </div>
</div>
{/if}
{foreach from=$COMMENTS item=html}
{$html}
{/foreach}