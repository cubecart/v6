{* Adjust the grid number if manufacturer names are long. *}
         <ul class="small-block-grid-1 medium-block-grid-3 large-block-grid-5">
{foreach from=$MANUFACTURERS item=manufacturer}
            <li><input type="checkbox" value="{$manufacturer.id}" id="manufacturer_{$manufacturer.id}" name="search[manufacturer][]" {$manufacturer.selected}><label for="manufacturer_{$manufacturer.id}">{$manufacturer.name}</label></li>
{/foreach}
         </ul>