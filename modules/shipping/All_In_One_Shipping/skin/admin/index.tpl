<style type="text/css">
  tr.show-removed td { text-decoration: line-through; color: #666; background: #ddd; }
  tr.show-removed .aios-editable { background: #ddd !important; }
</style>
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">

{if isset($DISPLAY_RATES)}

  <!-- TAB -->
  <div id="All_In_One_Shipping" class="tab_content">
    <h3>{$TITLE}</h3>
    {if $MULTIPLE_ZONES}
    <div>{$LANG.allinoneshipping.module_help_using_tabs}</div>
    {/if}
    <fieldset><legend>{$LANG.allinoneshipping.module_settings}</legend>
      <div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
      <div>
        <label for="range_weight">{$LANG.allinoneshipping.range_weight}</label>
        <span><input type="hidden" name="module[range_weight]" id="range_weight" class="toggle" value="{$MODULE.range_weight}" /></span>
        {$LANG.allinoneshipping.range_weight_description}
      </div>
      <div>
        <label for="range_subtotal">{$LANG.allinoneshipping.range_subtotal}</label>
        <span><input type="hidden" name="module[range_subtotal]" id="range_subtotal" class="toggle" value="{$MODULE.range_subtotal}" /></span>
        {$LANG.allinoneshipping.range_subtotal_description}
      </div>
      <div>
        <label for="range_items">{$LANG.allinoneshipping.range_items}</label>
        <span><input type="hidden" name="module[range_items]" id="range_items" class="toggle" value="{$MODULE.range_items}" /></span>
        {$LANG.allinoneshipping.range_items_description}
      </div>
      <div><label for="tax">{$LANG.catalogue.tax_type}</label>
        <span>
          <select name="module[tax]" id="tax">
            {foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
          </select>
        </span>
      </div>
      <div><label for="status">{$LANG.catalogue.tax_included}</label><span><input type="hidden" name="module[tax_included]" id="tax_included" class="toggle" value="{$MODULE.tax_included}" /></span></div>
    <div><label for="multiple_zones">{$LANG.allinoneshipping.multiple_zones}</label>
        <span>
          <select name="module[multiple_zones]" id="multiple_zones">
            <option value="all" {$SELECT_multiple_zones_all}>{$LANG.allinoneshipping.use_all_matching_zones}</option>
            <option value="first" {$SELECT_multiple_zones_first}>{$LANG.allinoneshipping.use_first_matching_zone}</option>
          </select>
        </span>
      </div>
      <div><label for="debug">{$LANG.allinoneshipping.debug}</label>
        <span>
          <select name="module[debug]" id="debug">
            <option value="0" {$SELECT_debug_0}>{$LANG.allinoneshipping.no_debug}</option>
            <option value="1" {$SELECT_debug_1}>{$LANG.allinoneshipping.enable_debug}</option>
            <option value="2" {$SELECT_debug_2}>{$LANG.allinoneshipping.verbose_debug}</option>
          </select>
        </span>
      </div>
    </fieldset>
    <fieldset><legend>{$LANG.allinoneshipping.advanced_settings}</legend>
      <div>
        <a href="#" onclick="$('#aios-advanced').toggle();return false">{$LANG.allinoneshipping.show_advanced_settings}</a><br />
      </div>
      <div id="aios-advanced">
        <div>
          <label>{$LANG.allinoneshipping.shipping_pricing}</label>
          <span><input type="hidden" name="module[use_flat]" id="use_flat" class="toggle" value="{$MODULE.use_flat}" /><label for="use_flat">{$LANG.allinoneshipping.use_flat}</label></span>
        </div>
        <div>
          <label class="spacer">&nbsp;</label>
          <span><input type="hidden" name="module[use_item]" id="use_item" class="toggle" value="{$MODULE.use_item}" /><label for="use_item">{$LANG.allinoneshipping.use_item}</label></span>
        </div>
        <div>
          <label class="spacer">&nbsp;</label>
          <span><input type="hidden" name="module[use_percent]" id="use_percent" class="toggle" value="{$MODULE.use_percent}" /><label for="use_percent">{$LANG.allinoneshipping.use_percent}</label></span>
        </div>
        <div>
          <label class="spacer">&nbsp;</label>
          <span><input type="hidden" name="module[use_weight]" id="use_weight" class="toggle" value="{$MODULE.use_weight}" /><label for="use_weight">{$LANG.allinoneshipping.use_weight}</label></span>
        </div>
      </div>
    </fieldset>
    <script type="text/javascript">
      document.getElementById('aios-advanced').style.display = 'none';
    </script>

    {if ! $MULTIPLE_ZONES}
    <h4>{$LANG.allinoneshipping.shipping_zones}</h4>
    <p>{$LANG.allinoneshipping.shipping_zones_description}</p>
    <h4>{$LANG.allinoneshipping.shipping_rates}</h4>
    {foreach from=$ZONES item=zone}
    {include file='admin/shipping_rates.tpl'}
    {/foreach}
    {/if}

  </div>

  <!-- TAB -->
  <div id="shipping_zones" class="tab_content">
    <h3>{$LANG.allinoneshipping.title_shipping_zones}</h3>
    <table class="list">
      <thead>
        <tr>
          <th>{$LANG.allinoneshipping.arrange}</th>
          <th>{$LANG.allinoneshipping.zone_name}</th>
          <th>{$LANG.allinoneshipping.countries}</th>
          <th>{$LANG.allinoneshipping.states_provinces_postcodes}</th>
          <th>{$LANG.allinoneshipping.shipping_rates}</th>
          <th>{$LANG.allinoneshipping.zone_actions}</th>
        </tr>
      </thead>
      <tbody class="reorder-list">
        {foreach from=$ZONES item=zone}
        {if $zone.id == 0}
      </tbody>
      <tbody class="list">
        {/if}
        <tr>
          <td align="center">
            {if $zone.id > 0}
            <a href="#" class="handle"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/updown.gif" title="{$LANG.ui.drag_reorder}" /></a>
            <input type="hidden" name="order[]" value="{$zone.id}" />
            {/if}
          </td>
          <td>{$zone.zone_name}</td>
          <td>{$zone.display_countries}</td>
          <td>{$zone.display_states} {$zone.display_postcodes}</td>
          <td><a href="{$zone.link_rates}">{$LANG.allinoneshipping.shipping_rates}</a></td>
          <td>
            {if $zone.link_edit}
            <a href="{$zone.link_edit}" class="aios-edit-zone"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/edit.png" alt="{$LANG.common.edit}" title="{$LANG.common.edit}" style="padding:0 5px;" /></a>
            {/if}
            {if $zone.link_delete}
            <a href="{$zone.link_delete}" class="aios-remove-zone" title="{$LANG.notification.confirm_delete}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/delete.png" alt="{$LANG.common.delete}" title="{$LANG.notification.confirm_delete}" style="padding:0 5px;" /></a>
            {/if}
          </td>
        </tr>
        {/foreach}
        <tr>
          <td colspan="6">
            <a href="{$LINK_ADD_SHIPPING_ZONE}"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/add.png" alt="" width="16" height="16" border="0" style="padding:0 5px;" />{$LANG.allinoneshipping.title_shipping_zone_add}</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  {if $MULTIPLE_ZONES}
  {foreach from=$ZONES item=zone}

  <!-- TAB -->
  <div id="zone_{$zone.id}" class="tab_content">
    <h3>{$zone.zone_name} <!--({$LANG.allinoneshipping.zone_id} {$zone.id})--></h3>
    {include file='admin/shipping_rates.tpl'}
  </div>

  {/foreach}
  {/if}

{/if}

{if isset($DISPLAY_FORM)}

  <div id="All_In_One_Shipping" class="tab_content">
    <h3>{$LEGEND}</h3>
    <fieldset>
      <input type="hidden" name="zone[id]" value="{$ZONE.id}" />
      <legend>{$LANG.allinoneshipping.title_shipping_zone_add}</legend>
      <div>
        <label for="zone_name">{$LANG.allinoneshipping.zone_name_description}</label>
        <span>
          <input id="zone_name" name="zone[zone_name]" type="text" value="{$ZONE.zone_name}" class="required" />
        </span>
      </div>
      <br />
      <div>
        <label for="zone_type_countries">{$LANG.allinoneshipping.what_will_this_zone_contain}</label>
        <span>
          <input id="zone_type_countries" name="zone_type" type="radio" value="C" onclick="display_div('C');" {if $ZONE_TYPE == 'C'}checked="checked"{/if}/> <label for="zone_type_countries">{$LANG.allinoneshipping.one_or_more_countries}</label>
        </span>
      </div>
      <div>
        <label class="spacer">&nbsp;</label>
        <span>
          <input id="zone_type_states" name="zone_type" type="radio" value="S" onclick="display_div('S');" {if $ZONE_TYPE == 'S'}checked="checked"{/if}/> <label for="zone_type_states">{$LANG.allinoneshipping.one_or_more_states}</label>
        </span>
      </div>
      <div>
        <label class="spacer">&nbsp;</label>
        <span>
          <input id="zone_type_postcodes" name="zone_type" type="radio" value="P" onclick="display_div('P');" {if $ZONE_TYPE == 'P'}checked="checked"{/if}/> <label for="zone_type_postcodes">{$LANG.allinoneshipping.one_or_more_postcodes}</label>
        </span>
      </div>
      <br />
      <div id="div_countries">
        <div>
          <label for="zone_countries_chzn">{$LANG.allinoneshipping.country_countries}</label>
        </div>
        <div>
          <select id="zone_countries" data-placeholder="{$LANG.allinoneshipping.choose_each_country}" name="zone_countries[]" multiple="multiple" class="chzn-select" style="width:350px;">
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.iso}" {if in_array($country.iso, $ZONE_COUNTRIES)}selected="selected"{/if}>{$country.name}</option>
            {/foreach}
          </select>
        </div>
      </div>
      <div id="div_states">
        <div>
          <label for="state_country">{$LANG.allinoneshipping.select_country}</label>
        </div>
        <div>
          <select name="state_country">
            <option value="">{$LANG.allinoneshipping.select_country}</option>
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.iso}" {if in_array($country.iso, $ZONE_COUNTRIES)}selected="selected"{/if}>{$country.name}</option>
            {/foreach}
          </select>
        </div>
        <div>
          <label for="zone_states">{$LANG.allinoneshipping.zone_states}</label>
        </div>
        <div>
          <select data-placeholder="{$LANG.allinoneshipping.choose_each_state}" name="zone_states[]" multiple="multiple" class="chzn-select" style="width:350px;">
            {foreach from=$STATES item=state}
            <option value="{$state.abbrev}" {if in_array($state.abbrev, $ZONE_STATES)}selected="selected"{/if}>{$state.name}</option>
            {/foreach}
          </select>
        </div>
      </div>
      <div id="div_postcodes">
        <div>
          <label for="postcode_country">{$LANG.allinoneshipping.select_country}</label>
        </div>
        <div>
          <select name="postcode_country" onchange="display_instructions(this);">
            <option value="">{$LANG.allinoneshipping.select_country}</option>
            {foreach from=$COUNTRIES item=country}
            <option value="{$country.iso}" {if in_array($country.iso, $ZONE_COUNTRIES)}selected="selected"{/if}>{$country.name}</option>
            {/foreach}
          </select>
        </div>
        <div>
          <label for="zone_postcodes">{$LANG.allinoneshipping.postal_code_regions}</label>
        </div>
        <div>
          <textarea name="zone_postcodes" style="width:250px;height:150px;">{$ZONE.postcodes}</textarea>
        </div>

        <div id="div_pc_instructions">
          {$LANG.allinoneshipping.postcode_zone_instructions}
        </div>
        <div id="div_pc_instructions_us">
          {$LANG.allinoneshipping.postcode_zone_instructions_us}
        </div>
        <div id="div_pc_instructions_au">
          {$LANG.allinoneshipping.postcode_zone_instructions_au}
        </div>
        <div id="div_pc_instructions_gb">
          {$LANG.allinoneshipping.postcode_zone_instructions_gb}
        </div>
        <div id="div_pc_instructions_ca">
          {$LANG.allinoneshipping.postcode_zone_instructions_ca}
        </div>

      <script type="text/javascript">
        // Show/hide div
        function show_hide_div(div_id, show) {
          element = document.getElementById(div_id);
          if (show == undefined) {
            if (element.style.display == 'none') show = 1;
            else show = 0;
          }
          if (show == 1) element.style.display = 'block';
          else element.style.display = 'none';
          return show;
        }
        function display_div(zone_ref) {
          show_hide_div('div_countries', zone_ref=='C');
          show_hide_div('div_states', zone_ref=='S');
          show_hide_div('div_postcodes', zone_ref=='P');
        }
        // Hide all zone divs initially
        display_div('{$ZONE_TYPE}');

        function display_instructions(el) {
          if (el) {
            country_iso = el.options[el.selectedIndex].value;
          } else {
            country_iso = '';
          }
          instructions = ['US', 'AU', 'GB', 'CA'];
          if (instructions.indexOf(country_iso) == -1) {
            other_country = true;
          } else {
            other_country = false;
          }
          show_hide_div('div_pc_instructions', other_country);
          show_hide_div('div_pc_instructions_us', country_iso=='US');
          show_hide_div('div_pc_instructions_au', country_iso=='AU');
          show_hide_div('div_pc_instructions_gb', country_iso=='GB');
          show_hide_div('div_pc_instructions_ca', country_iso=='CA');
        }
        // Display general instructions initially
        display_instructions();
      </script>
    </fieldset>
  </div>

{/if}

  <div class="form_control">
    <input type="hidden" name="save" value="{$FORM_HASH}" />
    <input type="hidden" name="previous-tab" id="previous-tab" value="" />
    {if isset($DISPLAY_RATES)}
    <input type="submit" value="{$LANG.allinoneshipping.save_all_changes}" />
    {else}
    <input type="submit" value="{$LANG.allinoneshipping.save}" />
    {/if}
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />

</form>
