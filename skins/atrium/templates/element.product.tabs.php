{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * Description / specification / quantity discounts / plugin tabs.
 *
 * ⚠ Plugin contract — KEEP BOTH LOOPS AND THEIR else BRANCHES:
 *   $PRODUCT_TABS_TITLES    either {content_id, title} or a raw <dd> string
 *   $PRODUCT_TABS_CONTENTS  either {content_id, content, content_class} or raw HTML
 * Older plugins emit raw markup, which is why the {else} branches print the
 * item unchanged.
 *
 * x-show, never x-if: x-show hides with CSS and leaves the description and
 * specification in the DOM for crawlers; x-if would remove them.
 *
 * Keep the ids product_info, product_spec and quantity_discounts — the
 * call-to-action links to #quantity_discounts.
 *}
{assign var=firstTab value=''}
{if !empty($PRODUCT.description)}{assign var=firstTab value='product_info'}{else}{assign var=firstTab value='product_spec'}{/if}

<div class="mt-12" x-data="ccTabs('{$firstTab}')">
   <div class="border-b border-ink-200">
      <nav class="-mb-px flex flex-wrap gap-x-6" role="tablist" aria-label="{$LANG.catalogue.product_info}">
         {if !empty($PRODUCT.description)}
         <button type="button" role="tab" @click="tab = 'product_info'"
                 :aria-selected="tab === 'product_info' ? 'true' : 'false'"
                 class="border-b-2 px-1 py-3 text-sm font-medium"
                 :class="tab === 'product_info' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-800'">
            {$LANG.catalogue.product_info}
         </button>
         {/if}
         <button type="button" role="tab" @click="tab = 'product_spec'"
                 :aria-selected="tab === 'product_spec' ? 'true' : 'false'"
                 class="border-b-2 px-1 py-3 text-sm font-medium"
                 :class="tab === 'product_spec' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-800'">
            {$LANG.common.specification}
         </button>
         {if !empty($PRODUCT.discounts)}
         <button type="button" role="tab" @click="tab = 'quantity_discounts'"
                 :aria-selected="tab === 'quantity_discounts' ? 'true' : 'false'"
                 class="border-b-2 px-1 py-3 text-sm font-medium"
                 :class="tab === 'quantity_discounts' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-800'">
            {$LANG.catalogue.quantity_discounts}
         </button>
         {/if}
         {foreach from=$PRODUCT_TABS_TITLES item=product_tab_title}
            {if isset($product_tab_title.content_id) && isset($product_tab_title.title)}
         <button type="button" role="tab" @click="tab = '{$product_tab_title.content_id}'"
                 :aria-selected="tab === '{$product_tab_title.content_id}' ? 'true' : 'false'"
                 class="border-b-2 px-1 py-3 text-sm font-medium"
                 :class="tab === '{$product_tab_title.content_id}' ? 'border-brand-600 text-brand-700' : 'border-transparent text-ink-500 hover:border-ink-300 hover:text-ink-800'">
            {$product_tab_title.title}
         </button>
            {else}
         {$product_tab_title}
            {/if}
         {/foreach}
      </nav>
   </div>

   <div class="py-6">
      {if !empty($PRODUCT.description)}
      <div id="product_info" role="tabpanel" x-show="tab === 'product_info'" class="prose-cc">{$PRODUCT.description}</div>
      {/if}

      <div id="product_spec" role="tabpanel" x-show="tab === 'product_spec'">
         <table class="text-sm">
            <tbody class="divide-y divide-ink-200">
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.catalogue.product_code}</td><td class="py-2 text-ink-800">{$PRODUCT.product_code}</td></tr>
               {if $PRODUCT.manufacturer}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.catalogue.manufacturer}</td><td class="py-2 text-ink-800">{$MANUFACTURER}</td></tr>
               {/if}
               {if $MANUFACTURER_GPSR}
               {* GPSR (EU General Product Safety Regulation) requires the manufacturer's
                  postal address and an EU contact: a legal requirement, do not drop it. *}
               <tr>
                  <td class="py-2 pe-4 align-top font-medium text-ink-700">{$LANG.catalogue.manufacturer_address}</td>
                  <td class="py-2 text-ink-800">
                     {if !empty($MANUFACTURER_GPSR.line1)}{$MANUFACTURER_GPSR.line1}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.line2)}{$MANUFACTURER_GPSR.line2}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.town)}{$MANUFACTURER_GPSR.town}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.state)}{$MANUFACTURER_GPSR.state}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.postcode)}{$MANUFACTURER_GPSR.postcode}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.country)}{$MANUFACTURER_GPSR.country}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.phone)}{$MANUFACTURER_GPSR.phone}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.email)}<a href="mailto:{$MANUFACTURER_GPSR.email}">{$MANUFACTURER_GPSR.email}</a><br>{/if}
                     {if !empty($MANUFACTURER_GPSR.contact_url)}<a href="{$MANUFACTURER_GPSR.contact_url}" target="_blank" rel="noopener">{$MANUFACTURER_GPSR.contact_url}</a>{/if}
                  </td>
               </tr>
               <tr>
                  <td class="py-2 pe-4 align-top font-medium text-ink-700">{$LANG.catalogue.manufacturer_eu_contact}</td>
                  <td class="py-2 text-ink-800">
                     {if !empty($MANUFACTURER_GPSR.eu_name)}{$MANUFACTURER_GPSR.eu_name}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_line1)}{$MANUFACTURER_GPSR.eu_line1}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_line2)}{$MANUFACTURER_GPSR.eu_line2}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_town)}{$MANUFACTURER_GPSR.eu_town}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_state)}{$MANUFACTURER_GPSR.eu_state}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_postcode)}{$MANUFACTURER_GPSR.eu_postcode}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_country)}{$MANUFACTURER_GPSR.eu_country}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_phone)}{$MANUFACTURER_GPSR.eu_phone}<br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_email)}<a href="mailto:{$MANUFACTURER_GPSR.eu_email}">{$MANUFACTURER_GPSR.eu_email}</a><br>{/if}
                     {if !empty($MANUFACTURER_GPSR.eu_contact_url)}<a href="{$MANUFACTURER_GPSR.eu_contact_url}" target="_blank" rel="noopener">{$MANUFACTURER_GPSR.eu_contact_url}</a>{/if}
                  </td>
               </tr>
               {/if}
               {if $PRODUCT.stock_level}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.catalogue.stock_level}</td><td class="py-2 tabular text-ink-800">{$PRODUCT.stock_level}</td></tr>
               {/if}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.common.condition}</td><td class="py-2 text-ink-800">{$PRODUCT.condition}</td></tr>
               {if $PRODUCT.product_weight > 0}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.common.weight}</td><td class="py-2 tabular text-ink-800">{$PRODUCT.product_weight}{$CONFIG.product_weight_unit|lower}</td></tr>
               {/if}
               {if $PRODUCT.product_width > 0}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.common.width}</td><td class="py-2 tabular text-ink-800">{floatval($PRODUCT.product_width)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td></tr>
               {/if}
               {if $PRODUCT.product_height > 0}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.common.height}</td><td class="py-2 tabular text-ink-800">{floatval($PRODUCT.product_height)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td></tr>
               {/if}
               {if $PRODUCT.product_depth > 0}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.common.depth}</td><td class="py-2 tabular text-ink-800">{floatval($PRODUCT.product_depth)}{if $PRODUCT.dimension_unit=='in'}&#8243;{else}{$PRODUCT.dimension_unit}{/if}</td></tr>
               {/if}
               {if $PRODUCT.digital > 0}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$LANG.catalogue.product_type_digital}</td><td class="py-2 text-ink-800">{$LANG.common.download}</td></tr>
               {/if}
               {if $PRODUCT.spec_array}
               {foreach from=$PRODUCT.spec_array key=spec_key item=spec_val}
               <tr><td class="py-2 pe-4 font-medium text-ink-700">{$spec_val.0}</td><td class="py-2 text-ink-800">{$spec_val.1}</td></tr>
               {/foreach}
               {/if}
            </tbody>
         </table>
         {if !empty($PRODUCT.spec_copy)}<div class="prose-cc mt-4">{$PRODUCT.spec_copy}</div>{/if}
      </div>

      {if !empty($PRODUCT.discounts)}
      <div id="quantity_discounts" role="tabpanel" x-show="tab === 'quantity_discounts'">
         <p class="text-sm text-ink-600">{$LANG.catalogue.quantity_discounts_explained}</p>
         <table class="mt-4 max-w-sm text-sm">
            <thead>
               <tr class="border-b border-ink-300">
                  <th class="py-2 text-start text-xs uppercase tracking-wider text-ink-600">{$LANG.common.quantity}</th>
                  <th class="py-2 text-end text-xs uppercase tracking-wider text-ink-600">{$LANG.catalogue.price_per_unit}</th>
               </tr>
            </thead>
            <tbody class="divide-y divide-ink-200">
               <tr><td class="py-2 tabular">1</td><td class="price py-2 text-end tabular">{if $PRODUCT.ctrl_sale}{$PRODUCT.sale_price}{else}{$PRODUCT.price}{/if}</td></tr>
               {foreach from=$PRODUCT.discounts item=discount}
               <tr><td class="py-2 tabular">{$discount.quantity}+</td><td class="price py-2 text-end tabular">{$discount.price}</td></tr>
               {/foreach}
            </tbody>
         </table>
      </div>
      {/if}

      {foreach from=$PRODUCT_TABS_CONTENTS item=product_tab_content}
         {if isset($product_tab_content.content_id) && isset($product_tab_content.content)}
      <div id="{$product_tab_content.content_id}" role="tabpanel"
           x-show="tab === '{$product_tab_content.content_id}'"
           class="{if !empty($product_tab_content.content_class)}{$product_tab_content.content_class}{else}prose-cc{/if}">{$product_tab_content.content}</div>
         {else}
      {$product_tab_content}
         {/if}
      {/foreach}
   </div>
</div>
