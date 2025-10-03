<div><label for="manu_name">{$LANG.catalogue.manufacturer}</label><span><input type="text" class="textbox required" id="manu_name" name="manufacturer[name]" value="{$EDIT.name}"></span></div>
<div><label for="manu_site">{$LANG.common.url}</label><span><input type="text" class="textbox" id="manu_site" name="manufacturer[URL]" value="{$EDIT.URL}"></span></div>
<div><label for="manu_line1">{$LANG.address.line1}</label><span><input type="text" class="textbox" id="manu_line1" name="manufacturer[line1]" value="{$EDIT.line1}"></span></div>
<div><label for="manu_line2">{$LANG.address.line2}</label><span><input type="text" class="textbox" id="manu_line2" name="manufacturer[line2]" value="{$EDIT.line2}"></span></div>
<div><label for="manu_town">{$LANG.address.town}</label><span><input type="text" class="textbox" id="manu_town" name="manufacturer[town]" value="{$EDIT.town}"></span></div>
<div><label for="country_list">{$LANG.address.country}</label>
    <span>
        <select name="manufacturer[country]" id="country-list" class="textbox">
        {foreach from=$COUNTRIES item=country}<option value="{$country.id}" {$country.selected}>{$country.name}</option>{/foreach}
        </select>
    </span>
</div>
<div><label for="manu_state">{$LANG.address.state}</label><span><input type="text" class="textbox" id="state-list" name="manufacturer[state]" value="{$EDIT.state}"></span></div>
<div><label for="manu_postcode">{$LANG.address.postcode}</label><span><input type="text" class="textbox" id="manu_postcode" name="manufacturer[postcode]" value="{$EDIT.postcode}"></span></div>
<div><label for="manu_email">{$LANG.common.email}</label><span><input type="text" class="textbox" id="manu_email" name="manufacturer[email]" value="{$EDIT.email}"></span></div>
<div><label for="manu_phone">{$LANG.address.phone}</label><span><input type="text" class="textbox" id="manu_phone" name="manufacturer[phone]" value="{$EDIT.phone}"></span></div>
<div><label for="manu_contact_url">{$LANG.address.contact_url}</label><span><input type="text" class="textbox" id="manu_contact_url" name="manufacturer[contact_url]" value="{$EDIT.contact_url}"></span></div>