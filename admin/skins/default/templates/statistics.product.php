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
 <div id="general" class="tab_content">
   {if $PRODUCT}
   <h3>{$PRODUCT.name} ({$PRODUCT.product_code})</h3>
   <table>
    <tr>
      {if !empty($PRODUCT.image)}<td rowspan="6"><img src="{$PRODUCT.image}" class="border" style="margin-right: 20px" /></td>{/if}
      <td>{$LANG.common.created}</td>
      <td>{$PRODUCT.date_added}</td>
    </tr>
    <tr>
      <td>{$LANG.common.updated}</td>
      <td>{$PRODUCT.updated}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.first_sale}</td>
      <td>{$PRODUCT.first_sale}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.last_sale}</td>
      <td>{$PRODUCT.last_sale}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.total_sales}</td>
      <td>{$PRODUCT.total_sales}</td>
    </tr>
    <tr>
      <td>{$LANG.statistics.sale_interval}</td>
      <td>{$PRODUCT.sale_interval}</td>
    </tr>
   </table>
   {else}
   <p>Product not found.</p>
   {/if}
</div>