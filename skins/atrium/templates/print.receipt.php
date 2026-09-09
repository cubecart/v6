{*
 * CubeCart v6 — Atrium skin
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *
 * ⚠ MANDATORY. Cubecart::_receipt() renders this unguarded through
 * $smarty->display(), NOT GUI::display(). Three consequences:
 *   1. No CSRF token is injected (there is no form here anyway).
 *   2. The HTML minifier does not run.
 *   3. element.css.php is NOT included — hence the inline stylesheet and no
 *      font CDN, so the invoice prints identically with no network.
 *
 * $STORE_LOGO is the INVOICES logo here, not the storefront one:
 * Cubecart::_receipt() assigns it from GUI::getLogo(true, 'invoices').
 * $LIST_ORDERS is an array that always contains exactly one order.
 *}
<!DOCTYPE html>
<html dir="{$TEXT_DIRECTION}" lang="{$HTML_LANG}">
<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title>{$PAGE_TITLE}</title>
   <style>
      {literal}
      *, *::before, *::after { box-sizing: border-box; }
      body {
         margin: 0; padding: 24px;
         font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
         font-size: 13px; line-height: 1.55; color: #111827; background: #fff;
      }
      .sheet { max-width: 800px; margin: 0 auto; }
      .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; margin-bottom: 28px; }
      .logo img { max-height: 70px; width: auto; }
      h1 { font-size: 20px; margin: 0 0 4px; }
      h2 { font-size: 14px; margin: 24px 0 8px; }
      .cols { display: flex; gap: 32px; flex-wrap: wrap; margin-bottom: 24px; }
      .col { flex: 1 1 220px; }
      .label { font-size: 10px; text-transform: uppercase; letter-spacing: .07em; color: #6b7280; margin-bottom: 4px; }
      table { width: 100%; border-collapse: collapse; margin-top: 8px; }
      th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
      th { font-size: 10px; text-transform: uppercase; letter-spacing: .07em; color: #374151; border-bottom: 1px solid #9ca3af; }
      td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
      tfoot td { border-bottom: none; padding: 4px 10px; }
      tfoot tr.grand td { border-top: 2px solid #111827; font-weight: 700; font-size: 15px; padding-top: 8px; }
      .muted { color: #6b7280; }
      /* Order-status rubber stamp. Hex, not the skin's oklch tokens: some
         print engines still choke on oklch(). Colours mirror
         css/src/theme.css --color-stamp-*; keep the two in step. */
      .order_status {
         position: relative;
         display: inline-block; margin: 30px 10px 0 0;
         padding: 9px 16px;
         /* Both frames inside the border box: the mask clips to it. */
         border: 1px solid currentColor; border-radius: 7px;
         color: #6b7280;
         font-size: 15px; font-weight: 800; line-height: 1.2;
         letter-spacing: .14em; text-transform: uppercase;
         opacity: .85; transform: rotate(6deg);
         /* Worn ink, as css/src/components.css. An engine that ignores
            masks prints the clean stamp. */
         -webkit-mask-image: url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='120'%20height='60'%3E%3Cfilter%20id='n'%3E%3CfeTurbulence%20type='fractalNoise'%20baseFrequency='0.9'%20numOctaves='3'%20stitchTiles='stitch'/%3E%3CfeColorMatrix%20values='0%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%201.3%200.3'/%3E%3C/filter%3E%3Crect%20width='120'%20height='60'%20filter='url%28%23n%29'/%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='260'%20height='110'%3E%3Cfilter%20id='n'%3E%3CfeTurbulence%20type='fractalNoise'%20baseFrequency='0.18'%20numOctaves='3'%20stitchTiles='stitch'/%3E%3CfeColorMatrix%20values='0%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%201.2%200.42'/%3E%3C/filter%3E%3Crect%20width='260'%20height='110'%20filter='url%28%23n%29'/%3E%3C/svg%3E");
         mask-image: url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='120'%20height='60'%3E%3Cfilter%20id='n'%3E%3CfeTurbulence%20type='fractalNoise'%20baseFrequency='0.9'%20numOctaves='3'%20stitchTiles='stitch'/%3E%3CfeColorMatrix%20values='0%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%201.3%200.3'/%3E%3C/filter%3E%3Crect%20width='120'%20height='60'%20filter='url%28%23n%29'/%3E%3C/svg%3E"), url("data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20width='260'%20height='110'%3E%3Cfilter%20id='n'%3E%3CfeTurbulence%20type='fractalNoise'%20baseFrequency='0.18'%20numOctaves='3'%20stitchTiles='stitch'/%3E%3CfeColorMatrix%20values='0%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%200%201.2%200.42'/%3E%3C/filter%3E%3Crect%20width='260'%20height='110'%20filter='url%28%23n%29'/%3E%3C/svg%3E");
         -webkit-mask-size: 120px 60px, 260px 110px;
         mask-size: 120px 60px, 260px 110px;
         -webkit-mask-composite: source-in;
         mask-composite: intersect;
         -webkit-print-color-adjust: exact; print-color-adjust: exact;
      }
      .order_status::before {
         content: ""; position: absolute; inset: 4px;
         border: 3px solid currentColor; border-radius: 3px;
      }
      .order_status_1 { color: #1d4ed8; }
      .order_status_2 { color: #c26a10; }
      .order_status_3 { color: #15803d; }
      .order_status_4, .order_status_5, .order_status_6 { color: #c02718; }
      .comments { margin-top: 20px; padding: 12px 14px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; }
      .foot { margin-top: 32px; padding-top: 14px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; text-align: center; }
      @media print {
         body { padding: 0; font-size: 12px; }
         .sheet { max-width: none; }
         tr { page-break-inside: avoid; }
      }
      {/literal}
   </style>
</head>
<!-- 3s delay lets the logo paint first. No braces in the handler: it sits
     outside the literal block, where Smarty parses them. -->
<body onload="setTimeout(window.print, 3000);">
<div class="sheet">
{foreach from=$LIST_ORDERS item=order}

   <div class="head">
      <div class="logo">
         <img src="{$STORE_LOGO}" alt="{$CONFIG.store_name}">
      </div>
      <div style="text-align:right">
         <h1>{$LANG.common.invoice}</h1>
         <div class="muted">
            {if $CONFIG.oid_mode=='i'}{$order.{$CONFIG.oid_col}}{else}{$order.cart_order_id}{/if}<br>
            {$order.order_date}
         </div>
         <span class="order_status order_status_{$order.status}">{$order.order_status}</span>
      </div>
   </div>

   <div class="cols">
      <div class="col">
         <div class="label">{$LANG.common.invoice}</div>
         {if !empty($order.company_name)}<strong>{$order.company_name}</strong><br>{/if}
         {$order.first_name|capitalize} {$order.last_name|capitalize}<br>
         {if !empty($order.line1)}{$order.line1|capitalize}<br>{/if}
         {if !empty($order.line2)}{$order.line2|capitalize}<br>{/if}
         {if !empty($order.town)}{$order.town|upper}<br>{/if}
         {if !empty($order.state)}{$order.state|upper}<br>{/if}
         {$order.postcode}<br>
         {if !empty($order.country)}{$order.country}{/if}
         {if !empty($order.w3w)}<div class="w3w">///<a href="https://what3words.com/{$order.w3w}">{$order.w3w}</a></div>{/if}
      </div>
      <div class="col">
         <div class="label">{$CONFIG.store_name}</div>
         {if !empty($STORE.address)}{$STORE.address|nl2br}<br>{/if}
         {if !empty($STORE.county)}{$STORE.county}<br>{/if}
         {if !empty($STORE.postcode)}{$STORE.postcode}<br>{/if}
         {if !empty($STORE.country)}{$STORE.country}<br>{/if}
         {if !empty($CONFIG.email_address)}{$CONFIG.email_address}<br>{/if}
         {if !empty($CONFIG.tax_number)}{$LANG.settings.tax_vat_number}: {$CONFIG.tax_number}{/if}
      </div>
   </div>

   <table>
      <thead>
         <tr>
            <th>{$LANG.common.product}</th>
            <th class="num">{$LANG.common.quantity}</th>
            <th class="num">{$LANG.catalogue.price_each}</th>
            <th class="num">{$LANG.common.price}</th>
         </tr>
      </thead>
      <tbody>
         {foreach from=$order.items item=item}
         <tr>
            <td>
               {$item.name}{if !empty($item.product_code)} <span class="muted">({$item.product_code})</span>{/if}
               {if !empty($item.options)}
               <div class="muted">{foreach from=$item.options item=option}{$option}<br>{/foreach}</div>
               {/if}
            </td>
            <td class="num">{$item.quantity}</td>
            <td class="num">{$item.price}</td>
            <td class="num">{$item.price_total}</td>
         </tr>
         {/foreach}
      </tbody>
      <tfoot>
         <tr>
            <td colspan="3" class="num">{$LANG.basket.total_sub}</td>
            <td class="num">{$order.subtotal}</td>
         </tr>
         {if isset($order.discount_type)}
         <tr>
            <td colspan="3" class="num">{$LANG.basket.total_discount}</td>
            <td class="num">{$order.discount}</td>
         </tr>
         {/if}
         <tr>
            <td colspan="3" class="num">
               {if !empty($order.ship_method)}{str_replace('_',' ',$order.ship_method)}{if !empty($order.ship_product)} ({$order.ship_product}){/if}{else}{$LANG.basket.shipping}{/if}
            </td>
            <td class="num">{$order.shipping}</td>
         </tr>
         {foreach from=$order.taxes item=tax}
         <tr>
            <td colspan="3" class="num">{$tax.name}</td>
            <td class="num">{$tax.value}</td>
         </tr>
         {/foreach}
         <tr class="grand">
            <td colspan="3" class="num">{$LANG.basket.total_grand}</td>
            <td class="num">{$order.total}</td>
         </tr>
      </tfoot>
   </table>

   {if !empty($order.customer_comments)}
   <div class="comments">
      <div class="label">{$LANG.common.comments}</div>
      {$order.customer_comments|nl2br}
   </div>
   {/if}

   <div class="foot">
      {$LANG.orders.title_thanks}
      {if !empty($STORE.address)}
      <div style="margin-top:6px">
         {$LANG.address.return_address}:
         {if !empty($STORE.address)}{$STORE.address}, {/if}
         {if !empty($STORE.county)}{$STORE.county}, {/if}
         {if !empty($STORE.postcode)}{$STORE.postcode} {/if}
         {if !empty($STORE.country)}{$STORE.country}{/if}
      </div>
      {/if}
   </div>

{/foreach}
</div>
</body>
</html>
