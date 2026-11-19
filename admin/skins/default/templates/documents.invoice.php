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
<form action="{$VAL_SELF}" method="post">
  <div id="general" class="tab_content">
		<h3>{$LANG.orders.invoice_editor}</h3>
		{* A whole HTML document with Smarty in it is edited as code. A visual editor cannot show
		   its <head> and has to rebuild the document to display it (#4742). *}
		<textarea name="content" id="invoice_html" class="textbox cc-code" data-cc-mode="smarty" data-cc-height="760" spellcheck="false" aria-label="{$LANG.orders.invoice_editor}">{$INVOICE_HTML|escape:"html"}</textarea>
  </div>
    {if isset($PLUGIN_TABS)}
  {foreach from=$PLUGIN_TABS item=tab}
  {$tab}
  {/foreach}
  {/if}
{include file='templates/element.hook_form_content.php'}
  <div class="form_control">
		<input type="hidden" name="previous-tab" id="previous-tab" value="">
		<input type="submit" value="{$LANG.common.save}"> <a href="?_g=documents&node=invoice&restore=1" class="delete" title="{$LANG.notification.confirm_restore}">{$LANG.common.restore_default}</a>
  </div>
</form>