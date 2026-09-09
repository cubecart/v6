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
 *
 * Settings declared by a skin in the <settings> block of its config.xml.
 * $SKIN_SETTING_FIELDS is built by admin/sources/plugins.skin_settings.inc.php;
 * every field is already typed and escaped there, and a select's options are
 * pre-marked with `selected`.
 *
 * The bool row uses class="toggle" because the ACP's own JS turns a hidden input
 * with that class into the switch used everywhere else in admin.
 *}
<form action="{$VAL_SELF}" method="post">
   <div id="skin_settings" class="tab_content">
      <h3>{$SKIN_NAME}</h3>
      <fieldset>
         <legend>{$LANG.module.config_settings}</legend>
         {foreach from=$SKIN_SETTING_FIELDS item=field}
         <div>
            <label for="skin_setting_{$field.name}">{$field.label}</label>
            <span>
            {if $field.type == 'bool'}
               <input type="hidden" name="skin_settings[{$field.name}]" id="skin_setting_{$field.name}" class="toggle" value="{if $field.value}1{else}0{/if}">
            {elseif $field.type == 'select'}
               <select name="skin_settings[{$field.name}]" id="skin_setting_{$field.name}">
                  {foreach from=$field.options item=option}
                  <option value="{$option.value}"{if $option.selected} selected="selected"{/if}>{$option.label}</option>
                  {/foreach}
               </select>
            {elseif $field.type == 'color'}
               {* Two controls, one value. The swatch is the picker; the text box
                  is how a brand hex gets pasted in, and it is the one that posts
                  so an empty value stays empty — <input type="color"> has no
                  empty state and would silently post #000000. *}
               <input type="color" class="skin-colour-picker" data-target="skin_setting_{$field.name}" value="{if $field.value}{$field.value}{else}#000000{/if}">
               <input type="text" name="skin_settings[{$field.name}]" id="skin_setting_{$field.name}" class="textbox skin-colour-hex" maxlength="7" placeholder="#rrggbb" value="{$field.value}">
            {else}
               <input type="text" name="skin_settings[{$field.name}]" id="skin_setting_{$field.name}" class="textbox" value="{$field.value}">
            {/if}
            </span>
            {* Trailing text after the control is how the ACP shows field help
               (see the shipping modules); there is no .hint class in its CSS. *}
            {if $field.description} {$field.description}{/if}
         </div>
         {/foreach}
      </fieldset>
   </div>
   <div class="form_control">
      <input type="hidden" name="token" value="{$SESSION_TOKEN}">
      <input type="submit" name="save" value="{$LANG.common.save}">
   </div>
</form>
{literal}
<script>
// Keep each colour swatch and its hex box in step. The hex box owns the value.
document.addEventListener('DOMContentLoaded', function () {
   document.querySelectorAll('.skin-colour-picker').forEach(function (picker) {
      var hex = document.getElementById(picker.getAttribute('data-target'));
      if (!hex) return;
      picker.addEventListener('input', function () { hex.value = picker.value; });
      hex.addEventListener('input', function () {
         // Ignore half-typed values; the picker only accepts a full #rrggbb.
         if (/^#[0-9a-f]{6}$/i.test(hex.value)) picker.value = hex.value;
      });
   });
});
</script>
{/literal}
