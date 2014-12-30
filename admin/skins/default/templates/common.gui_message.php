{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
{if isset($GUI_MESSAGE)}
<div id="gui_message">
  {if isset($GUI_MESSAGE.error)}
  <div class="warnText" title="{$LANG.common.click_to_close}">
	{$LANG.gui_message.errors_detected}
	<ul>
		{foreach from=$GUI_MESSAGE.error item=error}
	  	<li>{$error}</li>
	  	{/foreach}
	</ul>
  </div>
  {/if}
  {if isset($GUI_MESSAGE.notice)}
	{foreach from=$GUI_MESSAGE.notice item=notice}
  	<div class="infoText" title="{$LANG.common.click_to_close}">{$notice}</div>
	{/foreach}
  {/if}
</div>
{/if}