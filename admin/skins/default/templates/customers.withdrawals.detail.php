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
   <h3>{$LANG.withdrawal.admin_section_title} &mdash; {$ROW.reference} <span class="wd-status wd-status-{$ROW.status}">{$ROW.status_label}</span></h3>

   <p><a href="?_g=customers&node=withdrawals" class="button tiny">&laquo; {$LANG.withdrawal.admin_section_title}</a></p>

   <fieldset>
      <legend>{$LANG.common.request|default:'Request'}</legend>
      <table class="wd-detail-table">
         <tr><td class="wd-label width_20">{$LANG.common.reference|default:'Reference'}</td><td>{$ROW.reference}</td></tr>
         <tr><td class="wd-label">{$LANG.basket.order_date}</td><td>{$ROW.submitted_at_human}</td></tr>
         <tr><td class="wd-label">{$LANG.common.status}</td><td><span class="wd-status wd-status-{$ROW.status}">{$ROW.status_label}</span></td></tr>
         <tr><td class="wd-label">{$LANG.common.name}</td><td>{$ROW.name|escape}</td></tr>
         <tr><td class="wd-label">{$LANG.common.email}</td><td>{$ROW.email|escape}</td></tr>
         <tr><td class="wd-label">{$LANG.address.address}</td><td><pre style="white-space:pre-wrap;margin:0;font-family:inherit">{$ROW.address|escape}</pre></td></tr>
         <tr><td class="wd-label">{$LANG.orders.order_number}</td><td>{if $ROW.cart_order_id}<a href="?_g=orders&action=edit&cart_order_id={$ROW.cart_order_id|escape}">{$ROW.cart_order_id}</a>{else}&mdash;{/if}</td></tr>
         <tr><td class="wd-label">{$LANG.withdrawal.field_reported_delivery}</td><td>{if $ROW.reported_delivery}{$ROW.reported_delivery}{else}&mdash;{/if}</td></tr>
         <tr><td class="wd-label">{$LANG.withdrawal.field_statement}</td><td><pre style="white-space:pre-wrap;margin:0;font-family:inherit">{$ROW.statement|escape}</pre></td></tr>
         <tr><td class="wd-label">{$LANG.withdrawal.field_reason}</td><td>{if $ROW.reason}<pre style="white-space:pre-wrap;margin:0;font-family:inherit">{$ROW.reason|escape}</pre>{else}&mdash;{/if}</td></tr>
         <tr><td class="wd-label">{$LANG.common.ip|default:'IP'}</td><td>{$ROW.ip|escape}</td></tr>
         <tr><td class="wd-label">{$LANG.common.language|default:'Language'}</td><td>{$ROW.lang|escape}</td></tr>
         <tr><td class="wd-label">{$LANG.common.acknowledged|default:'Acknowledged'}</td><td>{if $ROW.acknowledged_at_human}{$ROW.acknowledged_at_human}{else}&mdash;{/if}</td></tr>
      </table>
   </fieldset>

   {if $ORDER_SUMMARY}
   <fieldset>
      <legend>{$LANG.orders.order_number} {$ORDER_SUMMARY.cart_order_id}</legend>
      <table class="wd-detail-table">
         <tr><td class="wd-label width_20">{$LANG.basket.order_date}</td><td>{$ORDER_SUMMARY.order_date_human}</td></tr>
         <tr><td class="wd-label">{$LANG.common.status}</td><td>{$ORDER_SUMMARY.status_text}</td></tr>
         <tr><td class="wd-label">{$LANG.basket.total}</td><td>{$ORDER_SUMMARY.total}</td></tr>
         {if $ORDER_SUMMARY.ship_tracking}<tr><td class="wd-label">{$LANG.orders.tracking_id|default:'Tracking'}</td><td>{$ORDER_SUMMARY.ship_tracking|escape}</td></tr>{/if}
         <tr><td class="wd-label">{$LANG.common.name}</td><td>{$ORDER_SUMMARY.first_name|escape} {$ORDER_SUMMARY.last_name|escape}</td></tr>
         <tr><td class="wd-label">{$LANG.common.email}</td><td>{$ORDER_SUMMARY.email|escape}</td></tr>
      </table>
      {if $ORDER_HISTORY}
      <h4 style="margin-top:1em">{$LANG.orders.title_order_status} {$LANG.common.log|default:'log'}</h4>
      <table class="wd-detail-table">
         <thead><tr><th>{$LANG.common.status}</th><th>{$LANG.basket.order_date}</th><th>{$LANG.common.by|default:'By'}</th></tr></thead>
         <tbody>
         {foreach from=$ORDER_HISTORY item=h}
            <tr><td>{$h.status_text}</td><td>{$h.updated_human}</td><td>{$h.initiator}</td></tr>
         {/foreach}
         </tbody>
      </table>
      {/if}
   </fieldset>
   {/if}

   {if $ROW.status == 'new'}
   <form action="{$VAL_SELF}" method="post">
      <fieldset>
         <legend>{$LANG.common.action|default:'Action'}</legend>
         <p><label for="decision_note">{$LANG.withdrawal.admin_decision_note}</label><br>
            <textarea name="decision_note" id="decision_note" rows="3" style="width:100%"></textarea></p>
         <p>
            <button type="submit" name="decision_action" value="accept" class="button">{$LANG.withdrawal.admin_action_accept}</button>
            <button type="submit" name="decision_action" value="reject" class="button delete">{$LANG.withdrawal.admin_action_reject}</button>
            <button type="submit" name="decision_action" value="resend_ack" class="button tiny">{$LANG.withdrawal.admin_resend_ack}</button>
         </p>
      </fieldset>
   </form>
   {elseif $ROW.status == 'accepted'}
   <form action="{$VAL_SELF}" method="post">
      <fieldset>
         <legend>{$LANG.common.action|default:'Action'}</legend>
         <p>{$LANG.common.decided_at|default:'Decided at'}: {$ROW.decision_at_human}{if $ROW.decided_by_human} &mdash; {$ROW.decided_by_human}{/if}</p>
         {if $ROW.decision_note}<p><strong>{$LANG.withdrawal.admin_decision_note}:</strong><br><pre style="white-space:pre-wrap;margin:0;font-family:inherit">{$ROW.decision_note|escape}</pre></p>{/if}
         <p><label for="decision_note">{$LANG.withdrawal.admin_decision_note}</label><br>
            <textarea name="decision_note" id="decision_note" rows="3" style="width:100%"></textarea></p>
         <p>
            <button type="submit" name="decision_action" value="refunded" class="button">{$LANG.withdrawal.admin_action_mark_refunded}</button>
            <button type="submit" name="decision_action" value="resend_ack" class="button tiny">{$LANG.withdrawal.admin_resend_ack}</button>
         </p>
      </fieldset>
   </form>
   {else}
   <fieldset>
      <legend>{$LANG.common.action|default:'Action'}</legend>
      <p>{$LANG.common.decided_at|default:'Decided at'}: {$ROW.decision_at_human}{if $ROW.decided_by_human} &mdash; {$ROW.decided_by_human}{/if}</p>
      {if $ROW.refunded_at_human}<p>{$LANG.withdrawal.status_refunded}: {$ROW.refunded_at_human}</p>{/if}
      {if $ROW.decision_note}<p><strong>{$LANG.withdrawal.admin_decision_note}:</strong><br><pre style="white-space:pre-wrap;margin:0;font-family:inherit">{$ROW.decision_note|escape}</pre></p>{/if}
   </fieldset>
   {/if}
</div>
