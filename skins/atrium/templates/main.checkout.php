{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Distraction-free checkout layout: no nav, no search, no sidebar.
 *
 * ⚠ USED AUTOMATICALLY, NOT OPT-IN. controllers/controller.index.inc.php picks
 * this over main.php whenever $_GET['_a'] is confirm | basket | gateway | cart |
 * checkout AND this file exists. Deleting it silently routes checkout back
 * through main.php with the full chrome — a design change, not a no-op.
 *
 * Checkout therefore bypasses main.php entirely: anything main.php provides must
 * be repeated here if checkout needs it (hence the duplicated offline banner).
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
      {* ⚠ Invisible reCAPTCHA (mode 3) bootstrap. Without it the protected
         forms on this layout silently produce no captcha token and every
         submission is rejected. Must follow element.js_head.php so jQuery
         exists for plugins that assume it. *}
      {include file='templates/content.recaptcha.head.php'}
   </head>
   <body class="min-h-screen bg-ink-50 text-ink-800 antialiased">
      {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}

      <a class="cc-skip" href="#main_content">{$LANG.common.skip_to_content|default:'Skip to content'}</a>

      {* Duplicated from main.php: checkout does not pass through it. *}
      {if $STORE_OFFLINE}
      <div class="bg-warn-600 px-4 py-2 text-center text-sm font-medium text-white" role="status">
         {$LANG.common.warning_offline}
      </div>
      {/if}

      <header class="border-b border-ink-200 bg-ink-100">
         <div class="cc-container flex h-16 items-center justify-between">
            <a href="{$ROOT_PATH}" class="flex items-center">
               <img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}" class="max-h-10 w-auto">
            </a>
            <span class="text-sm text-ink-500">{$LANG.common.checkout|default:'Checkout'}</span>
         </div>
      </header>

      <div class="{$SECTION_NAME}_wrapper cc-container py-8">
         <main id="main_content" class="mx-auto max-w-4xl">
            {include file='templates/box.errors.php'}
            {$CHECKOUT_PROGRESS}
            {$PAGE_CONTENT}
         </main>
      </div>

      <footer class="mt-16 border-t border-ink-200 bg-ink-100">
         <div class="cc-container py-6 text-sm text-ink-500">
            {$COPYRIGHT}
            {include file='templates/ccpower.php'}
         </div>
      </footer>

      {include file='templates/element.js_foot.php'}
      {$LIVE_HELP}
      {$ACP_WIDGET}
   </body>
</html>
