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
{if isset($GUI_MESSAGE.error)}
<div data-alert class="alert-box alert">
   {$LANG.gui_message.errors_detected}
   <ul class="nomarg no-bullet">
      {foreach from=$GUI_MESSAGE.error item=error}
      <li>{$error}</li>
      {/foreach}
   </ul>
   <a href="#" class="close">&times;</a>
</div>
{/if}
{if isset($GUI_MESSAGE.notice)}
<div data-alert class="alert-box success">
   <ul class="nomarg no-bullet">
      {foreach from=$GUI_MESSAGE.notice item=notice}
      <li>{$notice}</li>
      {/foreach}
   </ul>
   <a href="#" class="close">&times;</a>
</div>
{/if}
{if isset($GUI_MESSAGE.info)}
<div data-alert class="alert-box">
   <ul class="nomarg no-bullet">
      {foreach from=$GUI_MESSAGE.info item=info}
      <li>{$info}</li>
      {/foreach}
   </ul>
   <a href="#" class="close">&times;</a>
</div>
{/if}
<noscript>
   <div data-alert class="alert-box alert">
   <ul class="nomarg no-bullet">
      <li>{$LANG.catalogue.error_js_required}</li>
   </ul>
   <a href="#" class="close">&times;</a>
</div>
</noscript>