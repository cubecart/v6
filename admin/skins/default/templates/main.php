<!DOCTYPE html>
<html class="no-js" xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="utf-8">
   <head>
      <meta charset="utf-8">
      <title>{$LANG.dashboard.title_admin_cp}</title>
      <link rel="shortcut icon" href="{$STORE_URL}/favicon.ico" type="image/x-icon">
      <link href='//fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700' rel='stylesheet' type='text/css'>
      <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/layout.css?{$VERSION_HASH}" media="screen">
      <link rel="stylesheet" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/font-awesome.min.css?{$VERSION_HASH}">
      {foreach from=$HEAD_CSS item=style}
      <link rel="stylesheet" type="text/css" href="{$style}?{$VERSION_HASH}" media="screen">
      {/foreach}
      <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/styles/styles.php?{$VERSION_HASH}" media="screen">
      <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/dropzone.css" media="screen">
      {foreach from=$HEAD_JS item=js_src}
      <script type="text/javascript" src="{$js_src}?{$VERSION_HASH}"></script>
      {/foreach}
<script type="text/javascript">
{literal}
window.$zopim||(function(d,s){var z=$zopim=function(c){z._.push(c)},$=z.s=
d.createElement(s),e=d.getElementsByTagName(s)[0];z.set=function(o){z.set.
_.push(o)};z._=[];z.set._=[];$.async=!0;$.setAttribute("charset","utf-8");
$.src="https://v2.zopim.com/?2zg8JeryZ6qTz93mglrw2llJid6J2QEu";z.t=+new Date;$.
type="text/javascript";e.parentNode.insertBefore($,e)})(document,"script");
{/literal}
$zopim(function() {
   {if $CONFIG.hide_chat=="1"}
   $zopim.livechat.hideAll();
   {else}
   $zopim.livechat.button.show();
   $zopim.livechat.setName(document.getElementById("admin_id").textContent);
{/if}
});
{literal}
function rL () {
    var s = this.responseText=='1' ? 'online' : 'offline';
    $zopim(function() {
        $zopim.livechat.setStatus(s);
    });
}
var oR = new XMLHttpRequest();
oR.addEventListener("load", rL);
oR.open("GET", "https://agent.cubecart.com");
oR.send();
{/literal}
</script>
   </head>
   <body>
      <div id="header">
         <a href="?"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/logo.cubecart.png" width="126" height="23" id="logo"></a>
         <span class="user_info">{$LANG.settings.title_welcome_back} <a href="?_g=settings&node=admins&action=edit&admin_id={$ADMIN_UID}" id="admin_id">{$ADMIN_USER}</a> - <a href="?_g=logout&amp;token={$SESSION_TOKEN}">{$LANG.account.logout} <i class="fa fa-sign-out"></i></a></span>
      </div>
      <div id="wrapper">
         <div id="navigation">
            {include file='templates/common.search.php'}
            {include file='templates/common.navigation.php'}
         </div>
         {include file='templates/common.breadcrumb.php'}
         <div id="content">
            <div id="tab_control">
            {foreach from=$TABS item=tab}
               <div {if !empty($tab.tab_id)}id="{$tab.tab_id}" {/if}class="tab">
               {if !empty($tab.notify)}<span class="tab_notify">{$tab.notify}</span>{/if}
               <a href="{$tab.url}{$tab.target}"{if !empty($tab.accesskey)} accesskey="{$tab.accesskey}"{/if}{if !empty($tab.onclick)} onclick="{$tab.onclick}"{/if} target="{$tab.a_target}">{$tab.name}</a>
            </div>
            {/foreach}
         </div>
         <div id="content_body">
            {include file='templates/common.gui_message.php'}
            <div id="page_content">
               <div id="loading_content"><i class="fa fa-spinner fa-pulse"></i></div>
               {$DISPLAY_CONTENT}
            </div>
         </div>
         {include file='templates/ccpower.php'}
      </div>
      </div>
      <div style="display: none" id="val_admin_folder">{$SKIN_VARS.admin_folder}</div>
      <div style="display: none" id="val_admin_file">{$SKIN_VARS.admin_file}</div>
      <div style="display: none" id="val_skin_folder">{$SKIN_VARS.skin_folder}</div>
      <div style="display: none" id="val_admin_lang">{$val_admin_lang}</div>
      {include file='templates/element.welcome_tour.php'}
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery.min.js?{$VERSION_HASH}"></script>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-ui.min.js?{$VERSION_HASH}"></script>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/plugins.php?{$VERSION_HASH}"></script>
      <!-- Include CKEditor -->
      <script type="text/javascript" src="includes/ckeditor/ckeditor.js?{$VERSION_HASH}"></script>
      <script type="text/javascript" src="includes/ckeditor/adapters/jquery.js?{$VERSION_HASH}"></script>
      <script src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/dropzone.js?{$VERSION_HASH}"></script>
      <script>
         {literal}
         $(window).load(function() {
           $("#joyrideTour").joyride({
             autoStart: {/literal}{$TOUR_AUTO_START}{literal},
             postStepCallback: function (index, tip) {
               if (index == 6) {
                  $('<p><iframe src="//player.vimeo.com/video/118638908" width="500" height="313" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe></p>').insertAfter($(".joyride_tour_end p").last());
               }
             },
             postRideCallback: function (){
              $.ajax({url: "{/literal}{$SKIN_VARS.admin_file}{literal}?_g=settings&node=admins&tour_shown={/literal}{$ADMIN_UID}{literal}"});
             },
           });
         });
         {/literal}
      </script>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/admin.js?{$VERSION_HASH}"></script>
      {if isset($CLOSE_WINDOW)}
      <script type="text/javascript">
         $(document).ready(function () {
          setInterval(function() { window.close(); }, 1000);
         });
      </script>
      {/if}
      {foreach from=$EXTRA_JS item=js_src}
      <script type="text/javascript" src="{$js_src}?{$VERSION_HASH}"></script>
      {/foreach}
      {foreach from=$BODY_JS item=js_src}
      <script type="text/javascript" src="{$js_src}?{$VERSION_HASH}"></script>
      {/foreach}
   </body>
</html>