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
    <h3>{$LANG.search.gdpr_tools}</h3>
    <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
    <fieldset>
            <legend>{$LANG.search.title_gdpr_report}</legend>
            <div><label for="email">{$LANG.common.email}</label><span><input type="text" name="email" id="email" value="" class="textbox"></span></div>
            <div>{$LANG.search.gdpr_report_desc}</div>
        </fieldset>
        <input type="submit" value="{$LANG.form.create_report}" name="search">
        </form>
    <hr>
    <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
        <p>{$LANG.customer.delete_older_than|replace:'%s':'<input type="number" min="1" value="" class="number-center" name="customer_purge">'} <input type="submit" class="delete submit_confirm tiny" title="{$LANG.notification.confirm_continue}" value="{$LANG.common.go}"></p>
    </form>
    <hr>
    <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
        <p>
            <input type="hidden" name="no_order_purge" value="1">
            <input type="submit" class="delete submit_confirm uppercase" value="{$LANG.customer.delete_no_order}" title="{$LANG.notification.confirm_continue}" name="search">
        </p>
    </form>
    <hr>
    <form action="{$VAL_SELF}" method="post" enctype="multipart/form-data">
        <p>
            <input type="hidden" name="delete_guests" value="1">
            <input type="submit" class="delete submit_confirm uppercase" value="{$LANG.customer.delete_guests}" title="{$LANG.notification.confirm_continue}" name="search">
        </p>
    </form>
</div>