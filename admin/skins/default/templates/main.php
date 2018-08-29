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
      <link rel="stylesheet" type="text/css" href="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/styles/dropzone.css?{$VERSION_HASH}" media="screen">
      {foreach from=$HEAD_JS item=js_src}
      <script type="text/javascript" src="{$js_src}"></script>
      {/foreach}
   </head>
   <body>
      <div id="header">
         <a href="?"><img src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/images/logo.cubecart.png" width="158" height="30" id="logo"></a>
         <span class="user_info">{$LANG.settings.title_welcome_back} <a href="?_g=settings&node=admins&action=edit&admin_id={$ADMIN_UID}">{$ADMIN_USER}</a> - <a href="?_g=logout&amp;token={$SESSION_TOKEN}">{$LANG.account.logout} <i class="fa fa-sign-out"></i></a></span>
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
               <a href="{$tab.url}{$tab.target}"{if !empty($tab.accesskey)} accesskey="{$tab.accesskey}"{/if} target="{$tab.a_target}">{$tab.name}</a>
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
      <ol id="joyrideTour" style="display: none">
         <li data-button="{$LANG.dashboard.ok_go}">
            <h2>{$LANG.common.hi_casual} {$ADMIN_USER_FIRST_NAME}!</h2>
            <p>{$LANG.dashboard.tour_welcome}</p>
         </li>
         <li data-id="nav_settings">
            <h2>1. {$LANG.navigation.nav_settings_store}</h2>
            <p>{$LANG.dashboard.tour_1}</p>
         </li>
         <li data-id="nav_categories">
            <h2>2. {$LANG.navigation.nav_categories}</h2>
            <p>{$LANG.dashboard.tour_2}</p>
         </li>
         <li data-id="nav_products">
            <h2>3. {$LANG.navigation.nav_products}</h2>
            <p>{$LANG.dashboard.tour_3}</p>
         </li>
         <li data-id="nav_marketplace">
            <h2>4. {$LANG.navigation.nav_marketplace}</h2>
            <p>{$LANG.dashboard.tour_4}</p>
         </li>
         <li data-id="nav_plugins">
            <h2>5. {$LANG.navigation.nav_plugins}</h2>
            <p>{$LANG.dashboard.tour_5}</p>
         </li>
         <li data-id="nav_docs">
         <h2>6. {$LANG.dashboard.tour_6_title}</h2>
            <p>{$LANG.dashboard.tour_6}</p>
         </li>
         <li data-id="clear_cache_master">
            <h2>7. {$LANG.dashboard.tour_7_title}</h2>
            <p>{$LANG.dashboard.tour_7}</p>
         </li>
         <li class="joyride_tour_end" data-button="{$LANG.common.close}">
            <h2>{$LANG.common.thats_it}</h2>
            {$LANG.dashboard.tour_end}
         </li>
      </ol>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-1.11.2.min.js"></script>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/jquery-ui-1.11.2.min.js"></script>
      <script type="text/javascript" src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/plugins.php?{$VERSION_HASH}"></script>
      <!-- Include CKEditor -->
      <script type="text/javascript" src="includes/ckeditor/ckeditor.js"></script>
      <script type="text/javascript" src="includes/ckeditor/adapters/jquery.js"></script>
      <script src="{$SKIN_VARS.admin_folder}/skins/{$SKIN_VARS.skin_folder}/js/dropzone.js"></script>
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
      <script type="text/javascript" src="{$js_src}"></script>
      {/foreach}
      {foreach from=$BODY_JS item=js_src}
      <script type="text/javascript" src="{$js_src}"></script>
      {/foreach}
   </body>
</html>