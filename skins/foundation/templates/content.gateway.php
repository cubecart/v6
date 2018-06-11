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
{if !isset($TRANSFER)}
<h2>{$LANG.gateway.select}</h2>
<form id="gateway-select" action="{$VAL_SELF}" method="post">
   {if $GATEWAYS}
   <ul class="no-bullet">
      {foreach from=$GATEWAYS item=gateway}
      <li>
         <input name="gateway" type="radio" value="{$gateway.folder}" id="{$gateway.folder}" {$gateway.checked}>
         {if !empty($gateway.help)}
         <a href="{$gateway.help}" class="info" title="{$LANG.common.information}"><svg class="icon"><use xlink:href="#icon-info-circle"></use></svg></a>
         {/if}
         <label for="{$gateway.folder}">{$gateway.description}</label>
      </li>
      {/foreach}
   </ul>
   <div class="text-center"><input type="submit" value="{$LANG.common.continue}" class="button"></div>
   {else}
   <p>{$LANG.gateway.none_defined}</p>
   {/if}
</form>
{/if}
{if isset($TRANSFER)}
{if  $TRANSFER.mode == 'iframe'}
<iframe src="{$IFRAME_SRC}" frameborder="0" scrolling="auto" width="100%" height="500">
{$IFRAME_FORM}
{else}
<form id="gateway-transfer" action="{$TRANSFER.action}" method="{$TRANSFER.method}" target="{$TRANSFER.target}">
   {foreach from=$FORM_VARS key=name item=value}<input type="hidden" name="{$name}" value="{$value}">
   {/foreach}
   {if $TRANSFER.mode == 'automatic'}
   <div class="thickpad-top text-center">
      <p>{$LANG.gateway.transferring}</p>
      <p><svg class="icon-x3 icon-submit"><use xlink:href="#icon-spinner"></use></svg></p>
   </div>
   {elseif $TRANSFER.mode == 'manual'}
   <h2>{$LANG.gateway.amount_due}</h2>
   <p>{$LANG_AMOUNT_DUE}</p>
   {$FORM_TEMPLATE}
   {/if}
   {if !$DISPLAY_3DS}
   <div class="text-center"><input type="submit" class="button success" value="{$BTN_PROCEED}"></div>
   {/if}
   {foreach from=$AFFILIATES item=affiliate}
   {$affiliate}
   {/foreach}
   {/if}
</form>
{/if}
