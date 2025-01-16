{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2023. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<!doctype html>
<html class="no-js" xmlns="http://www.w3.org/1999/xhtml" dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}">
    <head>
      <title>{$META_TITLE}</title>
      {include file='templates/element.meta.php'}
      <link href="{$CANONICAL}" rel="canonical">
      <link href="{$ROOT_PATH}favicon.ico" rel="shortcut icon" type="image/x-icon">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      {include file='templates/element.css.php'}
      {include file='templates/content.recaptcha.head.php'}
      {include file='templates/element.js_head.php'}
   </head>
   <body class="bg-white">
   {foreach from=$BODY_JS_TOP item=js}{$js}{/foreach}


  <header class="relative">
    <nav aria-label="Top">
      <!-- Top navigation -->
      <div class="bg-gray-900">
        <div class="mx-auto flex h-10 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
          <!-- Currency selector -->
          {if is_array($CURRENCIES)}
          {if count($CURRENCIES)>1}
          <div class="hidden lg:block lg:flex-1">
          <div class="flex">
              <label for="desktop-currency" class="sr-only">Currency</label>
              <div class="group relative -ml-2 rounded-md border-transparent bg-gray-900 focus-within:ring-2 focus-within:ring-white">
                <select id="desktop-currency" name="currency" class="fn-dd-url flex items-center rounded-md border-transparent bg-gray-900 bg-none py-0.5 pl-2 pr-5 text-sm font-medium text-white focus:border-transparent focus:outline-none focus:ring-0 group-hover:text-gray-100">
                {foreach from=$CURRENCIES item=currency}
                  <option value="{$currency.url}"{if $currency.code==$CURRENT_CURRENCY.code} selected="selected"{/if}>{$currency.code}</option>
                 {/foreach}
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center">
                  <svg class="h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                  </svg>
                </div>
              </div>
            </div>
         </div>
          {else}
          <div class="hidden lg:block lg:flex-1"></div>
          {/if}
         {/if}
         <p class="flex-1 text-center text-sm font-medium text-white lg:flex-none">Get free delivery on orders over $100</p>
          <div class="lg:flex lg:flex-1 lg:items-center lg:justify-end lg:space-x-6">
            {if $IS_USER}
            <a href="{$STORE_URL}/index.php?_a=account"><span class="text-sm font-medium text-white hover:text-gray-100">{$CUSTOMER.first_name|capitalize}</span> <img class="inline-block w-8 h-8 rounded-full" src="https://secure.gravatar.com/avatar/{md5($CUSTOMER.email)}?d=mp&s=32" alt=""></a>
            {else}
            <a href="{$STORE_URL}/register{$CONFIG.seo_ext}" class="text-sm font-medium text-white hover:text-gray-100">{$LANG.account.register}</a>
            <span class="h-6 w-px bg-gray-600" aria-hidden="true"></span>
            <a href="{$STORE_URL}/login{$CONFIG.seo_ext}" class="text-sm font-medium text-white hover:text-gray-100">{$LANG.account.login}</a>
            {/if}
         </div>
        </div>
      </div>

      <!-- Secondary navigation -->
      <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="border-b border-gray-200">
            <div class="flex h-16 items-center justify-between">
              <!-- Logo (lg+) -->
              <div class="hidden lg:flex lg:items-center">
                <a href="{$CONFIG.store_url}">
                  <span class="sr-only">{$CONFIG.store_name}</span>
                  <img class="h-8 w-auto" src="{$STORE_LOGO}" alt="">
                </a>
              </div>

              <div class="hidden h-full lg:flex">
                <!-- Mega menus -->
                <div class="ml-8">
                  <div class="flex h-full justify-center space-x-8">
                    <div class="flex">
                      <div class="relative flex">
                        <!-- Item active: "border-indigo-600 text-indigo-600", Item inactive: "border-transparent text-gray-700 hover:text-gray-800" -->
                        <button type="button" class="relative z-10 -mb-px flex items-center border-b-2 border-transparent pt-px text-sm font-medium text-gray-700 transition-colors duration-200 ease-out hover:text-gray-800" aria-expanded="false">Women</button>
                      </div>

                      <!--
                        'Women' mega menu, show/hide based on flyout menu state.

                        Entering: "transition ease-out duration-200"
                          From: "opacity-0"
                          To: "opacity-100"
                        Leaving: "transition ease-in duration-150"
                          From: "opacity-100"
                          To: "opacity-0"
                      -->
                      <div class="absolute inset-x-0 top-full text-gray-500 sm:text-sm">
                        <!-- Presentational element used to render the bottom shadow, if we put the shadow on the actual panel it pokes out the top, so we use this shorter element to hide the top of the shadow -->
                        <div class="absolute inset-0 top-1/2 bg-white shadow" aria-hidden="true"></div>
                        
                      </div>
                    </div>
                    <div class="flex">
                      <div class="relative flex">
                        <!-- Item active: "border-indigo-600 text-indigo-600", Item inactive: "border-transparent text-gray-700 hover:text-gray-800" -->
                        <button type="button" class="relative z-10 -mb-px flex items-center border-b-2 border-transparent pt-px text-sm font-medium text-gray-700 transition-colors duration-200 ease-out hover:text-gray-800" aria-expanded="false">Men</button>
                      </div>
                      <!--
                        'Men' mega menu, show/hide based on flyout menu state.

                        Entering: "transition ease-out duration-200"
                          From: "opacity-0"
                          To: "opacity-100"
                        Leaving: "transition ease-in duration-150"
                          From: "opacity-100"
                          To: "opacity-0"
                      -->
                      <div class="absolute inset-x-0 top-full text-gray-500 sm:text-sm">
                        <div class="absolute inset-0 top-1/2 bg-white shadow" aria-hidden="true"></div>
                      </div>
                    </div>
                    <a href="#" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800">Company</a>
                    <a href="#" class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-800">Stores</a>
                  </div>
                </div>
              </div>

              <!-- Mobile menu and search (lg-) -->
              <div class="flex flex-1 items-center lg:hidden">
                <!-- Mobile menu toggle, controls the 'mobileMenuOpen' state. -->
                <button type="button" class="-ml-2 rounded-md bg-white p-2 text-gray-400">
                  <span class="sr-only">Open menu</span>
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                  </svg>
                </button>

                <!-- Search -->
                <a href="#" class="ml-2 p-2 text-gray-400 hover:text-gray-500">
                  <span class="sr-only">Search</span>
                  <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                  </svg>
                </a>
              </div>

              <!-- Logo (lg-) -->
              <a href="{$CONFIG.store_url}" class="lg:hidden">
                <span class="sr-only">{$CONFIG.store_name}</span>
                <img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}" class="h-8 w-auto">
              </a>

              <div class="flex flex-1 items-center justify-end">
                <div class="flex items-center lg:ml-8">
                  <div class="flex space-x-8">
                    <div class="hidden lg:flex">
                      <a href="#" class="-m-2 p-2 text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Search</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                      </a>
                    </div>
                  </div>

                  <span class="hidden lg:flex mx-4 h-6 w-px bg-gray-200 lg:mx-6" aria-hidden="true"></span>

                  <div class="flow-root">
                    <a href="#" class="group -m-2 flex items-center p-2">
                      <svg class="h-6 w-6 flex-shrink-0 text-gray-400 group-hover:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                      </svg>
                      <span class="ml-2 text-sm font-medium text-gray-700 group-hover:text-gray-800">0</span>
                      <span class="sr-only">items in cart, view bag</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
 
  
  {include file='templates/element.breadcrumb.php'}
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-6 mb-12">
  {include file='templates/element.notices.php'}
  {$PAGE_CONTENT}
  </div>


   	  
   </body>
</html>