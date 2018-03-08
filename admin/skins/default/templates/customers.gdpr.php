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
<form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
   <div id="general" class="tab_content">
    <h3>{$LANG.search.title_gdpr_report}</h3>
    <p>{$LANG.search.gdpr_report_desc}</p>
    <fieldset>
        <legend>{$LANG.common.search}</legend>
        <div><label for="email">{$LANG.common.email}</label><span><input type="text" name="email" id="email" value="" class="textbox"></span></div>
    </fieldset>
    <input type="submit" value="{$LANG.form.create_report}" name="search">
   </div>
</form>