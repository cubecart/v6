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
<!DOCTYPE html>
<html class="no-js" xmlns="http://www.w3.org/1999/xhtml" dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}">
    <head>
      <title>{$META_TITLE}</title>
      {include file='templates/element.meta.php'}
      <link href="{$CANONICAL}" rel="canonical">
      <link href="{$ROOT_PATH}favicon.ico" rel="shortcut icon" type="image/x-icon">
      {include file='templates/element.css.php'}
      {include file='templates/content.recaptcha.head.php'}
      {include file='templates/element.google_analytics.php'}
      {include file='templates/element.js_head.php'}
   </head>
   <body>
        {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}
        {include file='images/icon-sprites.svg'}
   	  {if $STORE_OFFLINE}
   	  <div data-alert class="alert-box alert">{$LANG.common.warning_offline}<a href="#" class="close">&times;</a></div>
   	  {/if}
      <div class="off-canvas-wrap" data-offcanvas>
         <div class="inner-wrap">
            {include file='templates/box.off_canvas.right.php'}
            {include file='templates/box.off_canvas.left.php'}
            {include file='templates/box.eu_cookie.php'}
            <div class="row marg-top" id="top_header">
               <div class="small-4 large-3 columns">
                  <a href="{$ROOT_PATH}" class="main-logo"><img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}"></a>
               </div>
               <div class="small-8 large-9 columns nav-boxes">
                  <div class="row" id="nav-actions">
                     <div class="small-12 columns">
                        <div class="right text-center">{include file='templates/box.basket.php'}</div>
                        <div class="right text-center show-for-small"><a class="left-off-canvas-toggle button white tiny" href="#"><svg class="icon icon-x2"><use xlink:href="#icon-bars"></use></svg></a> <a class="button white tiny show-small-search" href="#"><svg class="icon icon-x2"><use xlink:href="#icon-search"></use></svg></a></div>
                        {include file='templates/box.currency.php'}
                        {include file='templates/box.language.php'}
                        {include file='templates/box.session.php'}
                     </div>
                  </div>
                  <div class="row show-for-medium-up">
                     <div class="small-12 columns">{include file='templates/box.search.php'}</div>
                  </div>
               </div>
            </div>
            <div class="row hide" id="small-search">
               <div class="small-12 columns">
                  {include file='templates/box.search.php'}
               </div>
            </div>
            <div class="row">
               <div class="small-12 columns small-collapse">
                  {include file='templates/box.navigation.php'}
                  <div class="hide" id="val_lang_back">{$LANG.common.back}</div>
               </div>
            </div>
            <div class="row">
               <div class="small-12 columns small-collapse">
                  {include file='templates/element.breadcrumb.php'} 
               </div>
            </div>
            <div class="row {$SECTION_NAME}_wrapper">
               <div class="small-12 large-9 columns" id="main_content">
                  {include file='templates/box.errors.php'}
                  {include file='templates/box.progress.php'}
                  {$PAGE_CONTENT}
               </div>
               <div class="large-3 columns show-for-large-up" id="sidebar_left">
                  {include file='templates/box.featured.php'}
                  {include file='templates/box.popular.php'}
                  {include file='templates/box.sale_items.php'}
               </div>
               <a href="#" class="back-to-top"><span class="show-for-small-only"><svg class="icon"><use xlink:href="#icon-angle-up"></use></svg></span><span class="show-for-medium-up"><svg class="icon"><use xlink:href="#icon-angle-up"></use></svg> {$LANG.common.top}</span></a>
            </div>
            <footer>
               <div class="row">
                  <div class="medium-7 large-7 columns">
                     {include file='templates/box.documents.php'}
                     <div class="show-for-medium-up">{$COPYRIGHT}</div>
                  </div>
                  <div class="medium-5 large-5 columns">
                     {$SOCIAL_LIST}
                     <div class="row collapse">
                        <div class="large-12 columns">
                           {include file='templates/box.newsletter.php'}
                           <div class="show-for-small-only">{$COPYRIGHT}</div>
                        </div>
                     </div>
                  </div>
               </div>
            </footer>
            {include file='templates/element.js_foot.php'}
            {$LIVE_HELP}
            {$DEBUG_INFO}
            {include file='templates/box.skins.php'}
            <a class="exit-off-canvas"></a>
            {include file='templates/ccpower.php'}
         </div>
      </div>
      {include file='templates/element.markup.json-ld.php'}
      {include file='templates/modal.exit.php'}
   </body>
</html>