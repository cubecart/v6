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
 *}
  <div id="packaging-boxes" class="tab_content">
	<h3>{$LANG.settings.packaging_title}</h3>
	<p>{$LANG.settings.packaging_note}</p>
	<fieldset id="packaging-list" data-next-index="{$PACKAGING_BOXES|count}">
	  <legend>{$LANG.settings.packaging_boxes}</legend>
	  {foreach from=$PACKAGING_BOXES item=box key=i}
	  <div class="packaging-row">
		<span class="actions">
		  <a href="#" class="remove dynamic" title="{$LANG.messages.confirm_delete}">
			<i class="fa fa-trash" title="{$LANG.common.delete}"></i>
		  </a>
		</span>
		<input type="text" name="packaging_boxes[{$i}][name]" value="{$box.name|escape:'html'}" class="textbox" placeholder="{$LANG.settings.packaging_name}" style="width:150px">
		<input type="text" name="packaging_boxes[{$i}][l]" value="{$box.l}" class="textbox" size="5" placeholder="{$LANG.common.length_short}"> &#215;
		<input type="text" name="packaging_boxes[{$i}][w]" value="{$box.w}" class="textbox" size="5" placeholder="{$LANG.common.width_short}"> &#215;
		<input type="text" name="packaging_boxes[{$i}][h]" value="{$box.h}" class="textbox" size="5" placeholder="{$LANG.common.height_short}">
		{$PACKAGING_DIM_UNIT}
	  </div>
	  {/foreach}
	</fieldset>

	<fieldset>
	  <legend>{$LANG.settings.packaging_add}</legend>
	  <div class="nostripe">
		<input type="text" id="pkg_name" class="textbox" placeholder="{$LANG.settings.packaging_name}" style="width:150px">
		<input type="text" id="pkg_l" class="textbox" size="5" placeholder="{$LANG.common.length_short}"> &#215;
		<input type="text" id="pkg_w" class="textbox" size="5" placeholder="{$LANG.common.width_short}"> &#215;
		<input type="text" id="pkg_h" class="textbox" size="5" placeholder="{$LANG.common.height_short}">
		<span id="packaging-dim-unit">{$PACKAGING_DIM_UNIT}</span>
		<a href="#" id="pkg-add-btn"><i class="fa fa-plus-circle"></i> {$LANG.common.add}</a>
	  </div>
	</fieldset>
  </div>
