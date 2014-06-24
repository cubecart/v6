    <table class="list">
      <thead>
        <tr>
          <th>{$LANG.allinoneshipping.method_name_description}</th>
          <th>{$LANG.allinoneshipping.shipping_price}</th>
          {if $MODULE.range_weight}<th>{$LANG.allinoneshipping.weight_limits}</th>{/if}
          {if $MODULE.range_subtotal}<th>{$LANG.allinoneshipping.subtotal_limits}</th>{/if}
          {if $MODULE.range_items}<th>{$LANG.allinoneshipping.total_quantity_of_items}</th>{/if}
          <th colspan="2">{$LANG.allinoneshipping.rate_actions}</th>
        </tr>
      </thead>
      <tbody id="zone_{$zone.id}_rates">
        {if $RATES}
        {foreach from=$RATES item=rate}
        {if $rate.zone_id == $zone.id}
        <tr>
          <td><span class="aios-editable aios-text" name="rates[{$rate.id}][method_name]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.method_name}</span></td>
          <td>
            {$sep = ''}
            {if $MODULE.use_flat}
            {$sep}{$sep = '+'}
            <span class="aios-editable aios-number" name="rates[{$rate.id}][flat_rate]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.flat_rate}</span>
            {/if}
            {if $MODULE.use_item}
            {$sep}{$sep = '+'}
            <span class="aios-editable aios-number" name="rates[{$rate.id}][item_rate]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.item_rate}</span> {$LANG.allinoneshipping.per_item}
            {/if}
            {if $MODULE.use_percent}
            {$sep}{$sep = '+'}
            <span class="aios-editable aios-number" name="rates[{$rate.id}][percent_rate]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.percent_rate}</span> {$LANG.allinoneshipping.percent_of_subtotal}
            {/if}
            {if $MODULE.use_weight}
            {$sep}{$sep = '+'}
            <span class="aios-editable aios-number" name="rates[{$rate.id}][weight_rate]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.weight_rate}</span> {$LANG.allinoneshipping.per_weight}
            {/if}
          </td>
          {if $MODULE.range_weight}
          <td>
            <span class="aios-editable aios-number" name="rates[{$rate.id}][min_weight]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.min_weight}</span> -
            <span class="aios-editable aios-number" name="rates[{$rate.id}][max_weight]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.max_weight}</span>
          </td>
          {/if}
          {if $MODULE.range_subtotal}
          <td>
            <span class="aios-editable aios-number" name="rates[{$rate.id}][min_value]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.min_value}</span> -
            <span class="aios-editable aios-number" name="rates[{$rate.id}][max_value]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.max_value}</span>
          </td>
          {/if}
          {if $MODULE.range_items}
          <td>
            <span class="aios-editable aios-number" name="rates[{$rate.id}][min_items]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.min_items}</span> -
            <span class="aios-editable aios-number" name="rates[{$rate.id}][max_items]" title="{$LANG.allinoneshipping.click_to_edit}">{$rate.max_items}</span>
          </td>
          {/if}
          <td>
            <input type="hidden" name="update_rates[{$rate.id}]" value="0" size="1" />
            <a href="#" class="aios-edit" rel="update_rates[{$rate.id}]"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.allinoneshipping.edit_row}" title="{$LANG.allinoneshipping.edit_row}" width="16" height="16" border="0" style="padding:0 5px;" /></a>
            {* NOT FUNCTIONAL
            <a href="#" class="aios-undo-edit" rel="update_rates[{$rate.id}]"><img src="modules/shipping/All_In_One_Shipping/skin/images/undo.png" alt="{$LANG.allinoneshipping.cancel_edit_row}" title="{$LANG.allinoneshipping.cancel_edit_row}" width="16" height="16" border="0" style="padding:0 5px;" /></a>
            *}
          </td>
          <td>
            <input type="hidden" name="delete_rates[{$rate.id}]" value="0" size="1" />
            <a href="#" class="aios-remove" rel="delete_rates[{$rate.id}]"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.allinoneshipping.delete_row}" title="{$LANG.allinoneshipping.delete_row}" width="16" height="16" border="0" style="padding:0 5px;" /></a>
            <a href="#" class="aios-undo-remove" rel="delete_rates[{$rate.id}]"><img src="modules/shipping/All_In_One_Shipping/skin/images/undo.png" alt="{$LANG.allinoneshipping.cancel_delete_row}" title="{$LANG.allinoneshipping.cancel_delete_row}" width="16" height="16" border="0" style="padding:0 5px;" /></a>
          </td>
        </tr>
        {/if}
        {/foreach}
        {/if}
        <tr>
          <td colspan="7">
            <a href="#" class="aios-add"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="{$LANG.allinoneshipping.add_rows}" title="{$LANG.allinoneshipping.add_rows}" width="16" height="16" border="0" style="padding:0 5px;" />{$LANG.allinoneshipping.add_new_shipping_rates}</a>
          </td>
        </tr>
        {for $offset=0; $offset<5; $offset++}
        <tr class="aios-add-rates-row">
          <td><input type="text" name="add_rates[{$zone.id}][{$offset}][method_name]" size="25" value="{$NEW_RATE.method_name}" /></td>
          <td>
            {$sep = ''}
            {if $MODULE.use_flat}
            {$sep}{$sep = '+'}
            <input type="text" name="add_rates[{$zone.id}][{$offset}][flat_rate]" size="7" value="{$NEW_RATE.flat_rate}" />
            {/if}
            {if $MODULE.use_item}
            {$sep}{$sep = '+'}
            <input type="text" name="add_rates[{$zone.id}][{$offset}][item_rate]" size="7" value="{$NEW_RATE.item_rate}" /> {$LANG.allinoneshipping.per_item}
            {/if}
            {if $MODULE.use_percent}
            {$sep}{$sep = '+'}
            <input type="text" name="add_rates[{$zone.id}][{$offset}][percent_rate]" size="7" value="{$NEW_RATE.percent_rate}" /> {$LANG.allinoneshipping.percent_of_subtotal}
            {/if}
            {if $MODULE.use_weight}
            {$sep}{$sep = '+'}
            <input type="text" name="add_rates[{$zone.id}][{$offset}][weight_rate]" size="7" value="{$NEW_RATE.weight_rate}" /> {$LANG.allinoneshipping.per_weight}
            {/if}
          </td>
          {if $MODULE.range_weight}
          <td>
            <input type="text" name="add_rates[{$zone.id}][{$offset}][min_weight]" size="7" value="{$NEW_RATE.min_weight}" class="min-weight" /> -
            <input type="text" name="add_rates[{$zone.id}][{$offset}][max_weight]" size="7" value="{$NEW_RATE.max_weight}" class="max-weight" />
          </td>
          {/if}
          {if $MODULE.range_subtotal}
          <td>
            <input type="text" name="add_rates[{$zone.id}][{$offset}][min_value]" size="7" value="{$NEW_RATE.min_value}" class="min-value" /> -
            <input type="text" name="add_rates[{$zone.id}][{$offset}][max_value]" size="7" value="{$NEW_RATE.max_value}" class="max-value" />
          </td>
          {/if}
          {if $MODULE.range_items}
          <td>
            <input type="text" name="add_rates[{$zone.id}][{$offset}][min_items]" size="7" value="{$NEW_RATE.min_items}" class="min-items" /> -
            <input type="text" name="add_rates[{$zone.id}][{$offset}][max_items]" size="7" value="{$NEW_RATE.max_items}" class="max-items" />
          </td>
          {/if}
          <td>&nbsp;</td>
          <td>&nbsp;</td>
        </tr>
        {/for} 
      </tbody>
    </table>
