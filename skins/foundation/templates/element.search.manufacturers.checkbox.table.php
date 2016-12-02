{* Good for long manufacturers names. *}
         <table style="width:100%;">
            <tbody>
{foreach from=$MANUFACTURERS item=manufacturer}
               <tr>
                  <td><input style="margin:0.25rem 0;" type="checkbox" value="{$manufacturer.id}" id="manufacturer_{$manufacturer.id}" name="search[manufacturer][]" {$manufacturer.selected}><label for="manufacturer_{$manufacturer.id}">{$manufacturer.name}</label></td>
               </tr>
{/foreach}
            </tbody>
         </table>