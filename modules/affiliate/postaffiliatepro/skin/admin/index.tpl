<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
  <div id="postaffiliatepro" class="tab_content">
  	<h3><a href="http://www.qualityunit.com/postaffiliatepro/" target="_blank" title="{$LANG.postaffiliatepro.module_title}">{$TITLE}</a></h3>
  	<p>{$LANG.postaffiliatepro.descr}</p>
  	<fieldset>
      <legend>{$LANG.module.config_settings}</legend>
  	  <div>
        <label for="module_status">{$LANG.common.status}</label>
        <span><input type="hidden" id="module_status" name="module[status]" class="toggle" value="{$MODULE.status}" /></span>
      </div>
  	  <div>
        <label for="module_display">{$LANG.module.aff_track}</label>
        <span>
    		<select id="module_display" name="module[display]" class="textbox">
    		  <option value="1" {$SELECT_display_1}>{$LANG.module.aff_track_gateway}</option>
    		  <option value="2" {$SELECT_display_2}>{$LANG.module.aff_track_complete}</option>
    		</select>
  	    </span>
      </div>
  	  <div>
        <label for="price_mode">{$LANG.module.aff_sale}</label>
        <span>
    		<select id="price_mode" name="module[price_mode]" class="textbox">
    		  <option value="grandtotal" {$SELECT_price_mode_grandtotal}>{$LANG.module.aff_sale_grand}</option>
    		  <option value="subtotal" {$SELECT_price_mode_subtotal}>{$LANG.module.aff_sale_sub}</option>
    		</select>
    	  </span>
      </div>
  	  <div>
        <label for="module_url">{$LANG.postaffiliatepro.url}</label>
        <span><input type="text" id="module_url" name="module[URL]" class="textbox" value="{$MODULE.URL}" /> *e.g. http://www.mysite.com/pap4/</span>
      </div>
  	  <div>
        <label for="module_aid">{$LANG.postaffiliatepro.aid}</label>
        <span><input type="text" id="module_aid" name="module[aid]" class="textbox" value="{$MODULE.aid}" /> *relevant to Network version</span>
      </div>
  	</fieldset>
  </div>
  <div class="form_control">
	  <input type="submit" value="{$LANG.common.save}" class="submit" />
  </div>
  <input type="hidden" name="token" value="{$SESSION_TOKEN}" />
</form>