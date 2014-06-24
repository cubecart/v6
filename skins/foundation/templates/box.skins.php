{if $SKINS}
<div class="pad skin_selector">
<form action="{$VAL_SELF}" method="post" class="autosubmit nomarg">
   <div class="row">
      <div class="small-6 large-2 columns"><h4>Change Skin:</h4></div>
      <div class="small-6 large-3 end columns">
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
   </div>
   <input type="submit" value="submit" class="hide">
</form>
</div>
{/if}