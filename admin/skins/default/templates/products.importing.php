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
<form action="?_g=products&node=import&cycle={$DATA.next_cycle}" method="post" enctype="multipart/form-data">
  <div id="general" class="tab_content">
  <h3>{$LANG.catalogue.title_import}</h3>
  <p>{$LANG.catalogue.importing_progress|sprintf:$DATA.imported:$DATA.total}</p>
  <img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/loading.gif" alt="" class="autosubmit">
  <input type="submit" value="submit" name="{$LANG.common.go}">
  
  </div>
</form>