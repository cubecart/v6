{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2014. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   http://www.cubecart.com
 * Email:  sales@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div  id="box-search">
   <form action="{$STORE_URL}/search.html" id="search_form" method="get">
      <div class="row collapse">
         <div class="small-10 large-11 columns">
            <input name="search[keywords]" type="text" placeholder="{$LANG.search.input_default}" required>
         </div>
         <div class="small-2 large-1 columns">
            <button class="button postfix" type="submit" value="{$LANG.common.search}"><i class="fa fa-search"></i></button>
         </div>
      </div>
      <input type="hidden" name="_a" value="category">
   </form>
   <div class="hide" id="validate_search">{$LANG.search.enter_search_term}</div>
</div>