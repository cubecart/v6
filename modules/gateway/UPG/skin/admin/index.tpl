<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
	<div id="upg" class="tab_content">
  		<h3>{$TITLE}</h3>
		<p class="copyText">{$LANG.ccavenue.module_description}</p>
  		<fieldset><legend>{$LANG.module.cubecart_settings}</legend>
			<div><label for="status">{$LANG.common.status}</label><span><input type="hidden" name="module[status]" id="status" class="toggle" value="{$MODULE.status}" /></span></div>
			<div><label for="description">{$LANG.common.description} *</label><span><input name="module[desc]" id="description" class="textbox" type="text" value="{$MODULE.desc}" /></span></div>
      <div><label for="position">{$LANG.module.position}</label><span><input type="text" name="module[position]" id="position" class="textbox number" value="{$MODULE.position}" /></span></div>
			<div>
        <label for="scope">{$LANG.module.scope}</label>
        <span>
          <select name="module[scope]">
                  <option value="both" {$SELECT_scope_both}>{$LANG.module.both}</option>
                  <option value="main" {$SELECT_scope_main}>{$LANG.module.main}</option>
                  <option value="mobile" {$SELECT_scope_mobile}>{$LANG.module.mobile}</option>
              </select>
        </span>
      </div>
      <div><label for="shreference">{$LANG.upg.shreference}</label><span><input name="module[shreference]" id="shreference" class="textbox" type="text" value="{$MODULE.shreference}" /></span></div>
      <div><label for="checkcode">{$LANG.upg.checkcode}</label><span><input name="module[checkcode]" id="checkcode" class="textbox" type="text" value="{$MODULE.checkcode}" /></span></div>
      <div><label for="filename">{$LANG.upg.filename}</label><span><input name="module[filename]" id="filename" class="textbox" type="text" value="{$MODULE.filename}" placeholder="e.g. payment.html" /></span> <a href="?_g=modules&amp;type=gateway&amp;module=UPG&amp;sample=1">{$LANG.upg.download_example}</a></div>
  		</fieldset>
      <p>{$LANG.module.description_options}</p>
  		</div>
  		{$MODULE_ZONES}
  		<div class="form_control">
			<input type="submit" name="save" value="{$LANG.common.save}" />
  		</div>
  	<input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>