<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="USPS" class="tab_content">
	<h3><a href="http://www.usps.com" target="_blank">{$TITLE}</a></h3>
  	<p>{$LANG.usps.module_description}</p>

  	<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
	  <div><label>{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
	  <div><label>{$LANG.usps.username}</label><span><input type="text" name="module[username]" value="{$MODULE.username}" class="textbox" /></span></div>
	  <div><label>{$LANG.usps.postcode_origin}</label><span><input type="text" name="module[ziporigin]" value="{$MODULE.ziporigin}" class="textbox" /></span></div>
	  <div><label for="packagingWeight">{$LANG.usps.package_weight}</label><span><input name="module[packagingWeight]" id="packagingWeight" class="textbox number" type="text" value="{$MODULE.packagingWeight}" /></span></div>
	  <div><label>{$LANG.basket.shipping_handling}</label><span><input name="module[handling]" id="handling" class="textbox number" type="text" value="{$MODULE.handling}" /></span></div>
	  <div><label>{$LANG.catalogue.tax_type}</label>
		<span><select name="module[tax]" id="tax">
		{foreach from=$TAXES item=tax}<option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>{/foreach}
		</select></span>
	  </div>
	</fieldset>
	<fieldset>
		<legend>{$LANG.usps.title_shipping_config}</legend>
		<div>
			<label>{$LANG.usps.package_size}</label>
			<span>
				<select name="module[size]">
					<option value="REGULAR" {$SELECT_size_REGULAR}>{$LANG.usps.size_regular}</option>
					<option value="LARGE" {$SELECT_size_LARGE}>{$LANG.usps.size_large}</option>
					<option value="OVERSIZE" {$SELECT_size_OVERSIZE}>{$LANG.usps.size_oversize}</option>
				</select>
			</span>
		</div>
		<div>
			<label>{$LANG.usps.container}</label>
			<span>
				<select name="module[container]">
					<option value="VARIABLE" {$SELECT_container_VARIABLE}>{$LANG.usps.contain_variable}</option>
					<!-- Not been able to get Rectangualr/Nonrectangular working... nightmare
					<option value="RECTANGULAR" {$SELECT_container_RECTANGULAR}>Rectangular</option>
					<option value="NONRECTANGULAR" {$SELECT_container_NONRECTANGULAR}>Nonrectangular</option>
					-->
				</select>
			</span>
		</div>
		<div><label for="handling">{$LANG.common.size}</label>
		  <span>
			<input type="text" class="textbox number" name="module[height]" value="{$MODULE.height}" size="4" /> &times;
			<input type="text" class="textbox number" name="module[width]" value="{$MODULE.width}" size="4" /> &times;
			<input type="text" class="textbox number" name="module[length]" value="{$MODULE.length}" size="4" />
			({$LANG.common.height} &times; {$LANG.common.width} &times; {$LANG.common.length}) Inches
		  </span>
		</div>
		<div>
			<label>{$LANG.usps.machinable}</label>
			<span>
				<input type="hidden" name="module[machinable]" id="machinable" class="toggle" value="{$MODULE.machinable}" />
			</span>
		</div>
		
		{$LANG.usps.machinable_info}
	</fieldset>

	<fieldset><legend>{$LANG.usps.title_service_domestic}</legend>
		<!--
		0 - First-Class
		1 - Priority Mail
		2 - Express Mail Hold for Pickup
		3 - Express Mail PO to Addressee
		4 - Parcel Post
		5 - Bound Printed Matter
		6 - Media Mail
		7 - Library
		12 - First-Class Postcard Stamped
		13 - Express Mail Flat-Rate Envelope
		16 - Priority Mail Flat-Rate Envelope
		17 - Priority Mail Flat-Rate Box
		18 - Priority Mail Keys and IDs
		19 - First-Class Keys and IDs
		22 - Priority Mail Flat Rate Large Box
		23 - Express Mail Sunday/Holiday
		25 - Express Mail Flat-Rate Envelope Sunday/Holiday
		27 - Express Mail Flat-Rate Envelope Hold For Pickup integer
		-->
			<div>
				<label style="width: 350px">{$LANG.usps.service_first}</label>
				<span>
					<input type="hidden" name="module[class_id_0]" id="class_id_0" class="toggle" value="{$MODULE.class_id_0}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_priority}</label>
				<span>
					<input type="hidden" name="module[class_id_1]" id="class_id_1" class="toggle" value="{$MODULE.class_id_1}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_express_pickup}</label>
				<span>
					<input type="hidden" name="module[class_id_2]" id="class_id_2" class="toggle" value="{$MODULE.class_id_2}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_express_po}</label>
				<span>
					<input type="hidden" name="module[class_id_3]" id="class_id_3" class="toggle" value="{$MODULE.class_id_3}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_parcel_post}</label>
				<span>
					<input type="hidden" name="module[class_id_4]" id="class_id_4" class="toggle" value="{$MODULE.class_id_4}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_bound}</label>
				<span>
					<input type="hidden" name="module[class_id_5]" id="class_id_5" class="toggle" value="{$MODULE.class_id_5}" /></span>
				</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_media_mail}</label>
				<span>
					<input type="hidden" name="module[class_id_6]" id="class_id_6" class="toggle" value="{$MODULE.class_id_6}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_library}</label>
				<span>
					<input type="hidden" name="module[class_id_7]" id="class_id_7" class="toggle" value="{$MODULE.class_id_7}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_postcard_first}</label>
				<span>
					<input type="hidden" name="module[class_id_12]" id="class_id_12" class="toggle" value="{$MODULE.class_id_12}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_flat_envelope_express}</label>
				<span>
					<input type="hidden" name="module[class_id_13]" id="class_id_13" class="toggle" value="{$MODULE.class_id_13}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_flat_envelope_priority}</label>
				<span>
					<input type="hidden" name="module[class_id_16]" id="class_id_16" class="toggle" value="{$MODULE.class_id_16}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_flat_box_priority}</label>
				<span>
					<input type="hidden" name="module[class_id_17]" id="class_id_17" class="toggle" value="{$MODULE.class_id_17}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_keys_priority}</label>
				<span>
					<input type="hidden" name="module[class_id_18]" id="class_id_18" class="toggle" value="{$MODULE.class_id_18}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_keys_express}</label>
				<span>
					<input type="hidden" name="module[class_id_19]" id="class_id_19" class="toggle" value="{$MODULE.class_id_19}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_flat_box_priority_large}</label>
				<span>
					<input type="hidden" name="module[class_id_22]" id="class_id_22" class="toggle" value="{$MODULE.class_id_22}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_sunday_express}</label>
				<span>
					<input type="hidden" name="module[class_id_23]" id="class_id_23" class="toggle" value="{$MODULE.class_id_23}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_sunday_express_flat}</label>
				<span>
					<input type="hidden" name="module[class_id_25]" id="class_id_25" class="toggle" value="{$MODULE.class_id_25}" />
				</span>
			</div>
			<div>
				<label style="width: 350px">{$LANG.usps.service_flat_envelope_express_hold}</label>
				<span>
					<input type="hidden" name="module[class_id_27]" id="class_id_27" class="toggle" value="{$MODULE.class_id_27}" />
				</span>
			</div>
		</fieldset>

    	<fieldset>
    		<legend>{$LANG.usps.title_service_international}</legend>
    		<!--
			1 - Express Mail International
			2 - Priority Mail International
			4 - Global Express Guaranteed (Document and Non-document)
			5 - Global Express Guaranteed Document used
			6 - Global Express Guaranteed Non-Document Rectangular shape
			7 - Global Express Guaranteed Non-Document Non-Rectangular
			8 - Priority Mail Flat Rate Envelope
			9 - Priority Mail Flat Rate Box
			10 - Express Mail International Flat Rate Envelope
			11 - Priority Mail Large Flat Rate Box
			12 - Global Express Guaranteed Envelope
			13 - First Class Mail International Letters
			14 - First Class Mail International Flats
			15 - First Class Mail International Parcels
			21 - PostCards
    		-->
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_express}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_1]" id="intl_class_id_1" class="toggle" value="{$MODULE.intl_class_id_1}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_priority}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_2]" id="intl_class_id_2" class="toggle" value="{$MODULE.intl_class_id_2}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_guarantee_express}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_4]" id="intl_class_id_4" class="toggle" value="{$MODULE.intl_class_id_4}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_guarantee_used}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_5]" id="intl_class_id_5" class="toggle" value="{$MODULE.intl_class_id_5}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_guarantee_rect}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_6]" id="intl_class_id_6" class="toggle" value="{$MODULE.intl_class_id_6}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_guarantee_non}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_7]" id="intl_class_id_7" class="toggle" value="{$MODULE.intl_class_id_7}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_flat_envelope_priority}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_8]" id="intl_class_id_8" class="toggle" value="{$MODULE.intl_class_id_8}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_flat_box_priority}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_9]" id="intl_class_id_9" class="toggle" value="{$MODULE.intl_class_id_9}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_flat_envelope_express}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_10]" id="intl_class_id_10" class="toggle" value="{$MODULE.intl_class_id_10}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_flat_box_priority_large}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_11]" id="intl_class_id_11" class="toggle" value="{$MODULE.intl_class_id_11}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_guarantee_envelope}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_12]" id="intl_class_id_12" class="toggle" value="{$MODULE.intl_class_id_12}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_first_class_letter}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_13]" id="intl_class_id_13" class="toggle" value="{$MODULE.intl_class_id_13}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_first_class_flat}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_14]" id="intl_class_id_14" class="toggle" value="{$MODULE.intl_class_id_14}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_intl_first_class_parcel}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_15]" id="intl_class_id_15" class="toggle" value="{$MODULE.intl_class_id_15}" />
					</span>
				</div>
				<div>
				<label style="width: 350px">{$LANG.usps.service_postcard}</label>
					<span>
						<input type="hidden" name="module[intl_class_id_21]" id="intl_class_id_21" class="toggle" value="{$MODULE.intl_class_id_21}" />
					</span>
	  </div>
	</fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control"><input type="submit" name="save" value="{$LANG.common.save}" /></div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>