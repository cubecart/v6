<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="FedEx" class="tab_content">
    <h3><a href="http://www.fedex.com" target="_blank">{$TITLE}</a></h3>
    <p>{$LANG.fedex.module_description}</p>
    <fieldset>
      <legend>{$LANG.module.cubecart_settings}</legend>
      <div>
        <label for="status">{$LANG.common.status}</label>
        <span>
        <input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" />
        </span> </div>
      <div>
        <label for="account_no">{$LANG.fedex.account_no}</label>
        <span>
        <input name="module[accNo]" id="accNo" class="textbox" type="text" value="{$MODULE.accNo}" />
        </span> </div>
      <div>
        <label for="password">{$LANG.fedex.password}</label>
        <span>
        <input name="module[password]" id="password" class="textbox" type="text" value="{$MODULE.password}" />
        </span> </div>
      <div>
        <label for="key">{$LANG.fedex.key}</label>
        <span>
        <input name="module[key]" id="key" class="textbox" type="text" value="{$MODULE.key}" />
        </span> </div>
      <div>
        <label for="meter_no">{$LANG.fedex.meter_no}</label>
        <span>
        <input name="module[meterNo]" id="meterNo" class="textbox" type="text" value="{$MODULE.meterNo}" />
        </span> </div>
      <div>
        <label for="packagingWeight">{$LANG.fedex.package_weight}</label>
        <span>
        <input name="module[packagingWeight]" id="packagingWeight" class="textbox" type="text" value="{$MODULE.packagingWeight}" />
        </span></div>
      <div>
        <label>{$LANG.fedex.package_length}</label>
        <span>
        <input name="module[length]" id="length" class="textbox" type="text" value="{$MODULE.length}" />
        (Inches) </span> </div>
      <div>
        <label>{$LANG.fedex.package_width}</label>
        <span>
        <input name="module[width]" id="width" class="textbox" type="text" value="{$MODULE.width}" />
        (Inches) </span> </div>
      <div>
        <label>{$LANG.fedex.package_height}</label>
        <span>
        <input name="module[height]" id="height" class="textbox" type="text" value="{$MODULE.height}" />
        (Inches) </span> </div>
      <div>
        <label for="handling">{$LANG.basket.shipping_handling}</label>
        <span>
        <input name="module[handling]" id="handling" class="textbox" type="text" value="{$MODULE.handling}" />
        </span> </div>
      <div>
        <label for="tax">{$LANG.catalogue.tax_type}</label>
        <span>
        <select name="module[tax]" id="tax">
          
			  {foreach from=$TAXES item=tax}
          <option value="{$tax.id}" {$tax.selected}>{$tax.tax_name}</option>
          {/foreach}
			
        </select>
        </span> </div>
    </fieldset>
    <fieldset>
      <legend>{$LANG.fedex.title_parcel_origin}</legend>
      <div>
        <label for="line1">{$LANG.address.line1}</label>
        <span>
        <input type="text" name="module[line1]" value="{$MODULE.line1}" class="textbox" size="10" />
        {$LANG.common.eg} 10 Fed Ex Pkwy</span> </div>
      <div>
        <label for="city">{$LANG.address.town}</label>
        <span>
        <input type="text" name="module[city]" value="{$MODULE.city}" class="textbox" size="10" />
        {$LANG.common.eg} Memphis</span> </div>
      <div>
        <label for="state">{$LANG.address.state}</label>
        <span>
        <input type="text" name="module[state]" value="{$MODULE.state}" class="textbox" size="10" />
        {$LANG.common.eg} TN</span> </div>
      <div>
        <label for="country">{$LANG.address.country}</label>
        <span>
        <input type="text" name="module[country]" value="{$MODULE.country}" class="textbox" size="10" />
        {$LANG.common.eg} US</span> </div>
      <div>
        <label for="postcode">{$LANG.address.postcode}</label>
        <span>
        <input type="text" name="module[postcode]" value="{$MODULE.postcode}" class="textbox" size="10" />
        </span> </div>
    </fieldset>
    <fieldset>
      <legend>{$LANG.fedex.title_settings_shipping}</legend>
      <div>
        <label for="dropoffType">{$LANG.fedex.dropoff_method}</label>
        <span>
        <select name="module[dropoffType]">
          <option value="REGULAR_PICKUP" {$SELECT_dropoffType_REGULAR_PICKUP}>{$LANG.fedex.drop_regular}</option>
          <option value="REQUEST_COURIER" {$SELECT_dropoffType_REQUEST_COURIER}>{$LANG.fedex.drop_courier}</option>
          <option value="DROP_BOX" {$SELECT_dropoffType_DROP_BOX}>{$LANG.fedex.drop_dropbox}</option>
          <option value="BUSINESS_SERVICE_CENTER" {$SELECT_dropoffType_BUSINESS_SERVICE_CENTER}>{$LANG.fedex.drop_service}</option>
          <option value="STATION" {$SELECT_dropoffType_STATION}>{$LANG.fedex.drop_station}</option>
        </select>
        </span> </div>
    </fieldset>
    <fieldset>
      <legend>{$LANG.fedex.title_carrier_service}</legend>
      <p>{$LANG.fedex.carrier_service_info}</p>
      <table>
        <thead>
          <tr>
            <td>{$LANG.fedex.title_service}</td>
            <td>{$LANG.common.status}</td>
          </tr>
        </thead>
        <tbody class="list">
          <tr>
            <td>Europe First International Priority</td>
            <td align="center"><input type="hidden" name="module[FDXG_EUROPE_FIRST_INTERNATIONAL_PRIORITY]" id="FDXG_EUROPE_FIRST_INTERNATIONAL_PRIORITY" class="toggle" value="{$MODULE.FDXG_EUROPE_FIRST_INTERNATIONAL_PRIORITY}" /></td>
          </tr>
          <tr>
            <td>1 Day Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_1_DAY_FREIGHT]" id="FDXG_FEDEX_1_DAY_FREIGHT" class="toggle" value="{$MODULE.FDXG_FEDEX_1_DAY_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>2 Day</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_2_DAY]" id="FDXG_FEDEX_2_DAY" class="toggle" value="{$MODULE.FDXG_FEDEX_2_DAY}" /></td>
          </tr>
          <tr>
            <td>2 Day AM</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_2_DAY_AM]" id="FDXG_FEDEX_2_DAY_AM" class="toggle" value="{$MODULE.FDXG_FEDEX_2_DAY_AM}" /></td>
          </tr>
          <tr>
            <td>2 Day Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_2_DAY_FREIGHT]" id="FDXG_FEDEX_2_DAY_FREIGHT" class="toggle" value="{$MODULE.FDXG_FEDEX_2_DAY_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>3 Day Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_3_DAY_FREIGHT]" id="FDXG_FEDEX_3_DAY_FREIGHT" class="toggle" value="{$MODULE.FDXG_FEDEX_3_DAY_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>Express Saver</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_EXPRESS_SAVER]" id="FDXG_FEDEX_EXPRESS_SAVER" class="toggle" value="{$MODULE.FDXG_FEDEX_EXPRESS_SAVER}" /></td>
          </tr>
          <tr>
            <td>First Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_FIRST_FREIGHT]" id="FDXG_FEDEX_FIRST_FREIGHT" class="toggle" value="{$MODULE.FDXG_FEDEX_FIRST_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>Freight Economy</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_FREIGHT_ECONOMY]" id="FDXG_FEDEX_FREIGHT_ECONOMY" class="toggle" value="{$MODULE.FDXG_FEDEX_FREIGHT_ECONOMY}" /></td>
          </tr>
          <tr>
            <td>Freight Priority</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_FREIGHT_PRIORITY]" id="FDXG_FEDEX_FREIGHT_PRIORITY" class="toggle" value="{$MODULE.FDXG_FEDEX_FREIGHT_PRIORITY}" /></td>
          </tr>
          <tr>
            <td>Ground</td>
            <td align="center"><input type="hidden" name="module[FDXG_FEDEX_GROUND]" id="FDXG_FEDEX_GROUND" class="toggle" value="{$MODULE.FDXG_FEDEX_GROUND}" /></td>
          </tr>
          <tr>
            <td>First Overnight</td>
            <td align="center"><input type="hidden" name="module[FDXG_FIRST_OVERNIGHT]" id="FDXG_FIRST_OVERNIGHT" class="toggle" value="{$MODULE.FDXG_FIRST_OVERNIGHT}" /></td>
          </tr>
          <tr>
            <td>Ground Home Delivery</td>
            <td align="center"><input type="hidden" name="module[FDXG_GROUND_HOME_DELIVERY]" id="FDXG_GROUND_HOME_DELIVERY" class="toggle" value="{$MODULE.FDXG_GROUND_HOME_DELIVERY}" /></td>
          </tr>
          <tr>
            <td>International Economy</td>
            <td align="center"><input type="hidden" name="module[FDXG_INTERNATIONAL_ECONOMY]" id="FDXG_INTERNATIONAL_ECONOMY" class="toggle" value="{$MODULE.FDXG_INTERNATIONAL_ECONOMY}" /></td>
          </tr>
          <tr>
            <td>International Economy Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_INTERNATIONAL_ECONOMY_FREIGHT]" id="FDXG_INTERNATIONAL_ECONOMY_FREIGHT" class="toggle" value="{$MODULE.FDXG_INTERNATIONAL_ECONOMY_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>International First</td>
            <td align="center"><input type="hidden" name="module[FDXG_INTERNATIONAL_FIRST]" id="FDXG_INTERNATIONAL_FIRST" class="toggle" value="{$MODULE.FDXG_INTERNATIONAL_FIRST}" /></td>
          </tr>
          <tr>
            <td>International Prority</td>
            <td align="center"><input type="hidden" name="module[FDXG_INTERNATIONAL_PRIORITY]" id="FDXG_INTERNATIONAL_PRIORITY" class="toggle" value="{$MODULE.FDXG_INTERNATIONAL_PRIORITY}" /></td>
          </tr>
          <tr>
            <td>International Priority Freight</td>
            <td align="center"><input type="hidden" name="module[FDXG_INTERNATIONAL_PRIORITY_FREIGHT]" id="FDXG_INTERNATIONAL_PRIORITY_FREIGHT" class="toggle" value="{$MODULE.FDXG_INTERNATIONAL_PRIORITY_FREIGHT}" /></td>
          </tr>
          <tr>
            <td>Priority Overnight</td>
            <td align="center"><input type="hidden" name="module[FDXG_PRIORITY_OVERNIGHT]" id="FDXG_PRIORITY_OVERNIGHT" class="toggle" value="{$MODULE.FDXG_PRIORITY_OVERNIGHT}" /></td>
          </tr>
          <tr>
            <td>Smart Post</td>
            <td align="center"><input type="hidden" name="module[FDXG_SMART_POST]" id="FDXG_SMART_POST" class="toggle" value="{$MODULE.FDXG_SMART_POST}" /></td>
          </tr>
          <tr>
            <td>Standard Overnight</td>
            <td align="center"><input type="hidden" name="module[FDXG_STANDARD_OVERNIGHT]" id="FDXG_STANDARD_OVERNIGHT" class="toggle" value="{$MODULE.FDXG_STANDARD_OVERNIGHT}" /></td>
          </tr>
        </tbody>
      </table>
    </fieldset>
  </div>
  {$MODULE_ZONES}
  <div class="form_control">
    <input type="submit" name="save" value="{$LANG.common.save}" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>