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
{if $SKINS}
<form action="{$VAL_SELF}" method="post" class="autosubmit nomarg skin_selector thickpad-top" id="box-skin">
   <div class="row">
      <div class="small-6 columns text-right"><h4>Change Skin:</h4></div>
      <div class="small-5 columns">
         <select name="select_skin" class="auto_submit">
         {foreach from=$SKINS item=skin}
         {if isset($skin.styles)}
         {foreach from=$skin.styles item=style}
         <option value="{$skin.name}|{$style.directory}" {$style.selected}>{$skin.display} - {$style.name}</option>
         {/foreach}
         {else}
         <option value="{$skin.name}" {$skin.selected}>{$skin.display}</option>
         {/if}
         {/foreach}
         </select>
      </div>
      <div class="small-1 columns">
         <h4><a href="#" class="hide_skin_selector" title="{$LANG.common.close}"><svg class="icon"><use xlink:href="#icon-times"></use></svg></a></h4>
      </div>
   </div>
   <input type="submit" value="submit" class="hide">
</form>
{/if}