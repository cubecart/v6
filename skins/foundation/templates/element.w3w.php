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
<what3words-autosuggest id="{$as_id}" value="{$value}" />
<input type="hidden" name="{$input_name}" id="{$input_id}" value="{$value}" />
<script>
const {$as_id} = document.getElementById("{$as_id}");
{$as_id}.addEventListener("select", function(value) {
    document.getElementById("{$input_id}").value = value.detail;
});
</script>