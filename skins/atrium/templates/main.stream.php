{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Cubecart::_download() fetches this unguarded via GUI::display()
 * when a customer streams a digital download (?_a=download&s=1). A STANDALONE
 * DOCUMENT — its own <html>, not $PAGE_CONTENT.
 *
 * Cubecart::_download() assigns:
 *   $STREAM_URL  '?_a=download&accesskey=…'
 *   $DATA        {title, description, mimetype, …}  false when the link expired
 *   $TYPE        'audio' or 'video' — A DYNAMIC TAG NAME. Do not wrap it in a
 *                component that assumes a fixed element.
 *   $STREAM_HOOK plugin-supplied; never set by core, so it renders empty.
 *}
<!DOCTYPE html>
<html dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}"{if $SKIN_CUSTOM.colour_scheme == 'dark'} data-theme="dark"{/if}>
   <head>
      <title>{$META_TITLE}</title>
      {include file='templates/element.meta.php'}
      <link href="{$CANONICAL}" rel="canonical">
      <link href="{$ROOT_PATH}favicon.ico" rel="shortcut icon" type="image/x-icon">
      {include file='templates/element.css.php'}
      {include file='templates/element.js_head.php'}
   </head>
   <body class="min-h-screen bg-ink-50 text-ink-800 antialiased">
      {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}
      <div class="cc-container max-w-4xl py-10">
      {if $DATA}
         <h1 class="text-2xl font-semibold tracking-tight text-ink-900">{$DATA.title}</h1>
         {if !empty($DATA.description)}
         <p class="mt-2 text-ink-600">{$DATA.description|nl2br}</p>
         {/if}
         <div class="mt-6 overflow-hidden rounded-cc-lg border border-ink-200 bg-ink-950">
            <{$TYPE} class="w-full" controls autoplay>
               <source src="{$STREAM_URL}" type="{$DATA.mimetype}">
            </{$TYPE}>
         </div>
         {$STREAM_HOOK}
      {else}
         <div class="cc-alert cc-alert-warn" role="alert">
            <p>{$LANG.filemanager.media_expired}</p>
         </div>
      {/if}
      </div>
      {include file='templates/element.js_foot.php'}
   </body>
</html>
