{* Good for a really high number of manufacturers names. *}
         <style scoped>{* Need to override Chosen's hard rule for text input. May be solved in a later version of Chosen. *}
          {literal}.chosen-container-multi .chosen-choices li.search-field input[type="text"]{height:auto;}{/literal}
         </style>
         <select id="search_manufacturers" data-placeholder="{$LANG.common.please_select}" multiple="multiple" class="chzn-select" style="width:100%;" name="search[manufacturer][]">
{foreach from=$MANUFACTURERS item=manufacturer}
            <option {$manufacturer.selected} value="{$manufacturer.id}">{$manufacturer.name}</option>
{/foreach}
         </select>