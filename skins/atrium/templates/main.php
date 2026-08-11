{*
 * CubeCart v6 — Atrium skin
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ── ⚠ SMARTY / ALPINE HOUSE RULE ─────────────────────────────────────────────
 * Write every Alpine component as a FUNCTION REFERENCE, never an inline object
 * literal — Smarty parses the braces and the template dies:
 *
 *     GOOD:  x-data="ccDrawer('menuOpen')"
 *     BAD:   x-data="{open:false}"        <- Smarty parses this, fatal
 *     BAD:   :class="{'a':b}"             <- same
 *
 * Smarty's $auto_literal (true by default in Smarty.class.php, never overridden
 * here) spares "{" followed by whitespace, so a spaced literal happens to
 * survive. Do not rely on it. For class toggling use the ternary form:
 *     :class="open ? 'block' : 'hidden'"
 * ─────────────────────────────────────────────────────────────────────────────
 *}
<!DOCTYPE html>
<html dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}"{if $SKIN_CUSTOM.colour_scheme == 'dark'} data-theme="dark"{/if}>
   <head>
      <title>{$META_TITLE}</title>
      {include file='templates/element.meta.php'}
      <link href="{$CANONICAL}" rel="canonical">
      <link href="{$ROOT_PATH}favicon.ico" rel="shortcut icon" type="image/x-icon">
      {include file='templates/element.css.php'}
      {if $SKIN_CUSTOM.colour_scheme == 'auto'}
      {* Must stay inline and ahead of first paint, or the wrong theme flashes. *}
      <script>{literal}(function(){try{if(matchMedia('(prefers-color-scheme: dark)').matches){document.documentElement.setAttribute('data-theme','dark');}}catch(e){}})();{/literal}</script>
      {/if}
      {include file='templates/element.js_head.php'}
      {* ⚠ Invisible reCAPTCHA (mode 3) bootstrap. Every layout that can contain
         a protected form needs it, or those forms silently produce no captcha
         token and every submission is rejected. Must follow element.js_head.php
         so jQuery exists for plugins that assume it. *}
      {include file='templates/content.recaptcha.head.php'}
   </head>
   <body class="min-h-screen bg-ink-50 text-ink-800 antialiased">
      {* MUST be first inside <body>: GUI::__construct() queues the script that
         writes the cc_browser capability cookie used for bot protection. *}
      {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}

      <a class="cc-skip" href="#main_content">{$LANG.common.skip_to_content|default:'Skip to content'}</a>

      {if $STORE_OFFLINE}
      <div class="bg-warn-600 px-4 py-2 text-center text-sm font-medium text-white" role="status">
         {$LANG.common.warning_offline}
      </div>
      {/if}

      {if $CORE_BELOW_MIN_VERSION}
      <div class="bg-danger-600 px-4 py-2 text-center text-sm font-medium text-white" role="alert">
         Atrium requires CubeCart 6.7.6 or newer. This store is running {$CC_VERSION}.
      </div>
      {/if}

      <header class="sticky top-0 z-30 border-b border-ink-200 bg-ink-100/95 backdrop-blur">
         <div class="cc-container flex h-16 min-w-0 items-center gap-3 sm:gap-4">

            <button type="button"
                    class="cc-btn cc-btn-ghost -ml-2 p-2 lg:hidden"
                    x-data="ccDrawer('menuOpen')"
                    @click="open = true"
                    :aria-expanded="open ? 'true' : 'false'"
                    aria-controls="cc-mobile-nav">
               <span class="cc-sr-only">{$LANG.navigation.expand_for_more}</span>
               <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                  <path d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" stroke-linecap="round"/>
               </svg>
            </button>

            {* min-w-0, deliberately not shrink-0: a flex item defaults to
               min-width:auto, so an oversized merchant logo would refuse to
               shrink and push the header past the viewport. *}
            <a href="{$ROOT_PATH}" class="flex min-w-0 items-center">
               <img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}" class="max-h-10 w-auto max-w-[50vw] object-contain sm:max-w-none">
            </a>

            <div class="ms-auto hidden w-full max-w-md lg:block">
               {include file='templates/box.search.php'}
            </div>

            {* Core pre-renders these boxes to HTML and assigns the result:
                 $SESSION       <- box.session.php   GUI::_displaySessionBox()
                 $CURRENCY      <- box.currency.php  GUI::_displayCurrencySwitch()
                 $LANGUAGE      <- box.language.php  GUI::_displayLanguageSwitch()
                 $SHOPPING_CART <- box.basket.php    GUI::displaySideBasket()
               ⚠ All four are templateExists()-guarded except box.basket.php,
               which is fetched unguarded — that template MUST exist. *}
            <div class="ms-auto flex shrink-0 items-center gap-1 sm:gap-2">
               {$SESSION}
               {$CURRENCY}
               {$LANGUAGE}
               {$SHOPPING_CART}
            </div>
         </div>

         {* ⚠ box.navigation.php must be {include}d, never replaced with
            {$CATEGORIES}. GUI::_displayNavigation() fetches the template first,
            while $CATEGORIES is still unset, so the {else} branch builds the
            tree and assigns it; this include then takes the {if $CATEGORIES}
            branch and prints it. Printing {$CATEGORIES} on its own renders
            nothing on a cache miss. *}
         <div class="hidden border-t border-ink-200 lg:block">
            <div class="cc-container py-1">
               {include file='templates/box.navigation.php'}
            </div>
         </div>
      </header>

      <div x-data="ccDrawer('menuOpen')" x-cloak>
         <div x-show="open" x-transition.opacity
              class="fixed inset-0 z-40 bg-ink-950/50 lg:hidden"
              @click="close()" aria-hidden="true"></div>
         <div id="cc-mobile-nav" x-show="open" role="dialog" aria-modal="true"
              x-trap.noscroll="open"
              @keydown.escape.window="close()"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="-translate-x-full"
              x-transition:enter-end="translate-x-0"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="translate-x-0"
              x-transition:leave-end="-translate-x-full"
              class="cc-nav-mobile fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] overflow-y-auto border-r border-ink-200 bg-ink-100 p-4 lg:hidden">
            <div class="flex items-center justify-between">
               <span class="text-sm font-semibold text-ink-900">{$LANG.common.menu|default:'Menu'}</span>
               <button type="button" class="cc-btn cc-btn-ghost p-2" @click="close()">
                  <span class="cc-sr-only">{$LANG.common.close|default:'Close'}</span>
                  <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                     <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/>
                  </svg>
               </button>
            </div>

            <div class="mt-4">{include file='templates/box.search.php'}</div>

            {* Same box.navigation.php as the desktop bar; the .cc-nav-mobile
               class on the drawer is what restyles it into a stacked accordion
               in components.css. *}
            <div class="mt-4 border-t border-ink-200 pt-4">
               {include file='templates/box.navigation.php'}
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-ink-200 pt-4 md:hidden">
               {$CURRENCY}
               {$LANGUAGE}
            </div>
         </div>
      </div>

      <div class="{$SECTION_NAME}_wrapper cc-container py-8">
         {include file='templates/element.breadcrumb.php'}
         <div class="lg:flex lg:gap-10">
            <main id="main_content" class="min-w-0 flex-1">
               {include file='templates/box.errors.php'}
               {$CHECKOUT_PROGRESS}
               {$PAGE_CONTENT}
            </main>

            {* Each box self-guards on its own data, so this aside can render
               empty; the .cc-sidebar:empty rule in components.css is what drops
               its width when that happens. *}
            <aside class="cc-sidebar mt-10 space-y-6 lg:mt-0 lg:w-72 lg:shrink-0">
               {include file='templates/box.featured.php'}
               {include file='templates/box.popular.php'}
               {include file='templates/box.sale_items.php'}
            </aside>
         </div>
      </div>

      <footer class="mt-16 border-t border-ink-200 bg-ink-100">
         <div class="cc-container py-10">
            <div class="grid gap-10 md:grid-cols-2">
               {* $DOCUMENTS (GUI::_displayDocuments()) is a DATA array that
                  box.documents.php consumes. $SOCIAL_LIST (GUI::_displaySocial())
                  is already-rendered HTML — print it, and do not also {include}
                  element.social.php or the block appears twice. *}
               {include file='templates/box.documents.php'}
               <div class="space-y-8 md:text-right">
                  {include file='templates/box.newsletter.php'}
                  {$SOCIAL_LIST}
               </div>
            </div>
            <div class="mt-10 border-t border-ink-200 pt-6 text-sm text-ink-500">
               {$COPYRIGHT}
               {include file='templates/ccpower.php'}
            </div>
         </div>
      </footer>

      {* Product / Offer / AggregateRating / BreadcrumbList structured data.
         Must stay server-rendered: this is what Google reads for rich results. *}
      {include file='templates/element.markup.json-ld.php'}

      {include file='templates/element.js_foot.php'}
      {$LIVE_HELP}
      {$SKIN_SELECT}
      {$ACP_WIDGET}
   </body>
</html>
