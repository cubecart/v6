{*
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 *}
<div id="general" class="tab_content">
   <h3>{$LANG.withdrawal.admin_section_title}</h3>

   <fieldset class="width_40">
      <legend>{$LANG.common.filter}</legend>
      <div>
         <label class="narrow">{$LANG.common.status}</label>
         <select onchange="window.location='?_g=customers&node=withdrawals&filter_status='+this.value">
            <option value="">{$LANG.form.none}</option>
            <option value="new"{if $FILTER_STATUS == 'new'} selected{/if}>{$LANG.withdrawal.status_new}</option>
            <option value="accepted"{if $FILTER_STATUS == 'accepted'} selected{/if}>{$LANG.withdrawal.status_accepted}</option>
            <option value="rejected"{if $FILTER_STATUS == 'rejected'} selected{/if}>{$LANG.withdrawal.status_rejected}</option>
            <option value="refunded"{if $FILTER_STATUS == 'refunded'} selected{/if}>{$LANG.withdrawal.status_refunded}</option>
         </select>
      </div>
   </fieldset>

   {if $ROWS}
   <table>
      <thead>
         <tr>
            <th>{$LANG.common.reference|default:'Reference'}</th>
            <th>{$LANG.basket.order_date}</th>
            <th>{$LANG.common.status}</th>
            <th>{$LANG.orders.order_number}</th>
            <th>{$LANG.common.name}</th>
            <th>{$LANG.common.email}</th>
            <th></th>
         </tr>
      </thead>
      <tbody>
      {foreach from=$ROWS item=row}
         <tr>
            <td><a href="?_g=customers&node=withdrawals&id={$row.id}">{$row.reference}</a></td>
            <td>{$row.submitted_at_human}</td>
            <td><span class="wd-status wd-status-{$row.status}">{$row.status_label}</span></td>
            <td>{if $row.cart_order_id}<a href="?_g=orders&action=edit&cart_order_id={$row.cart_order_id|escape}">{$row.cart_order_id}</a>{else}&mdash;{/if}</td>
            <td>{$row.name|escape}</td>
            <td>{$row.email|escape}</td>
            <td><a href="?_g=customers&node=withdrawals&id={$row.id}" class="button tiny">{$LANG.common.view_details}</a></td>
         </tr>
      {/foreach}
      </tbody>
   </table>
   {$PAGINATION}
   {else}
   <p>{$LANG.withdrawal.admin_no_records}</p>
   {/if}
</div>
