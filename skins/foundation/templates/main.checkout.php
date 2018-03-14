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
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link href="{$CANONICAL}" rel="canonical">
      <link href="{$ROOT_PATH}/favicon.ico" rel="shortcut icon" type="image/x-icon">
      <link href="//fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet" type='text/css'>
      {assign var=css_input value=['skins/foundation/css/normalize.css','skins/foundation/css/foundation.css','skins/foundation/css/cubecart.css','skins/foundation/css/cubecart.common.css','skins/foundation/css/cubecart.helpers.css','skins/foundation/css/jquery.bxslider.css','skins/foundation/css/jquery.bxslider.css','skins/foundation/css/jquery.chosen.min.css']}
      {foreach from=$CSS key=css_keys item=css_files}
      {$css_input[] = $css_files}
      {/foreach}
      {combine input=$css_input output='cache/css.foundation.css' age='604800' debug=false}
      <meta name="description" content="{if isset($META_DESCRIPTION)}{$META_DESCRIPTION}{/if}">
      <meta name="keywords" content="{if isset($META_KEYWORDS)}{$META_KEYWORDS}{/if}">
      <meta name="robots" content="index, follow">
      <meta name="generator" content="cubecart">
      {if $FBOG}
      <meta property="og:image" content="{$PRODUCT.thumbnail}">
      <meta property="og:url" content="{$VAL_SELF}">
      {/if}
      {include file='templates/content.recaptcha.head.php'}
      <script src="{$ROOT_PATH}/skins/{$SKIN_FOLDER}/js/vendor/modernizr.min.js"></script>
      <script src="{$ROOT_PATH}/skins/{$SKIN_FOLDER}/js/vendor/jquery.js"></script>
      {include file='templates/element.google_analytics.php'}
      {foreach from=$HEAD_JS item=js}{$js}{/foreach}
   </head>
   <body>
      {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}
      {include file='images/icon-sprites.svg'}
      <div class="off-canvas-wrap" data-offcanvas>
         <div class="inner-wrap">
            {include file='templates/box.off_canvas.left.php'}
            {include file='templates/box.eu_cookie.php'}
            <div class="row marg-top" id="top_header">
               <div class="small-4 large-3 columns">
                  <a href="{$ROOT_PATH}" class="main-logo"><img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}"></a>
               </div>
               <div class="small-8 large-9 columns">
                  <div class="row" id="nav-actions">
                     <div class="small-12 columns">
                        <div class="right text-center show-for-small"><a class="left-off-canvas-toggle button white tiny" href="#"><svg class="icon icon-x2"><use xlink:href="#icon-bars"></use></svg></a></div>
                        <div class="right text-right show-for-medium-up">{include file='templates/box.session.php'}</div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="row {$SECTION_NAME}_wrapper">
               <div class="small-12 large-12 columns small-collapse">
               	{include file='templates/box.progress.php'}
               </div>
               <div class="small-12 large-12 columns">
                  {include file='templates/box.errors.php'}
                  {$PAGE_CONTENT}
               </div>
            </div>
            <footer>
               <div class="row">
                  <div class="medium-7 large-7 columns">
                     {include file='templates/box.documents.php'}
                     <div class="show-for-medium-up">{$COPYRIGHT}</div>
                  </div>
                  <div class="medium-5 large-5 columns">
                     {$SOCIAL_LIST}
                     <div class="show-for-small-only row collapse">
                        <div class="large-12 columns">
                           {$COPYRIGHT}
                        </div>
                     </div>
                  </div>
               </div>
            </footer>
            <script src="{$ROOT_PATH}/skins/{$SKIN_FOLDER}/js/vendor/jquery.rating.min.js" type="text/javascript"></script>
            <script src="{$ROOT_PATH}/skins/{$SKIN_FOLDER}/js/vendor/jquery.validate.min.js" type="text/javascript"></script>
            <script src="{$ROOT_PATH}/skins/{$SKIN_FOLDER}/js/vendor/jquery.cookie.min.js" type="text/javascript"></script>
            {foreach from=$BODY_JS item=js}{$js}{/foreach}
            {foreach from=$JS_SCRIPTS key=k item=script}
            <script src="{$ROOT_PATH}/{$script|replace:'\\':'/'}" type="text/javascript"></script>
            {/foreach}
            <script>
               {literal}
               $(document).foundation({equalizer:{equalize_on_stack:true}});
               {/literal}
            </script>
            {$LIVE_HELP}
            {$DEBUG_INFO}
            {$SKIN_SELECT}
            <a class="exit-off-canvas"></a>
            {include file='templates/ccpower.php'}
         </div>
      </div>
      {include file='templates/element.markup.json-ld.php'}
   </body>
</html>