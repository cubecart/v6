<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2026. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */
if (!defined('CC_INI_SET')) {
    die('Access Denied');
}

/**
 * Stock audit log
 *
 * Records every change core PHP makes to a stock level, so "why is my stock
 * wrong" has an answer. Writes made by raw SQL or by plugins are out of scope
 * and will not appear.
 *
 * Always on: a log a merchant has to switch on in advance is empty at the only
 * moment they ever want it. It keeps itself in check by pruning old entries on
 * a small share of writes, the same way the 404 and request logs do.
 *
 * Nothing here may interrupt a sale. Every entry point swallows its own
 * failures: an unwritten log line is a nuisance, a failed checkout is not.
 */
class StockLog
{
    /**
     * How long an entry is kept. A year covers the seasonal "we were sure we had
     * more than that last Christmas" question, which is the longest lookback a
     * merchant realistically asks for.
     */
    const RETENTION_DAYS = 365;

    /** Sources, so the admin filter and the templates agree on the vocabulary. */
    const SOURCE_ORDER      = 'order';        // taken or returned by an order status change
    const SOURCE_ADMIN      = 'admin';        // edited by hand in the control panel
    const SOURCE_IMPORT     = 'import';       // product import
    const SOURCE_API        = 'api';          // REST API
    const SOURCE_ROLLUP     = 'rollup';       // recalculated from the option matrix

    /**
     * Every source, for the admin filter dropdown.
     *
     * @return array
     */
    public static function sources()
    {
        return array(
            self::SOURCE_ORDER,
            self::SOURCE_ADMIN,
            self::SOURCE_IMPORT,
            self::SOURCE_API,
            self::SOURCE_ROLLUP,
        );
    }

    /**
     * Record one stock movement.
     *
     * @param int $product_id
     * @param int|null $matrix_id option row, when the change was to option stock
     * @param int $change signed: negative took stock, positive returned it
     * @param int|null $stock_after resulting level, null when not read back
     * @param string $source one of the SOURCE_* constants
     * @param string|null $cart_order_id order responsible, when there is one
     * @param string $note short free text, e.g. the status transition
     * @return bool
     */
    public static function record($product_id, $matrix_id, $change, $stock_after, $source, $cart_order_id = null, $note = '')
    {
        if ((int)$product_id < 1 || (int)$change === 0) {
            return false;
        }

        self::_prune();

        try {
            // Database::insert() passes a PHP null through sqlSafe(), which casts it
            // to '' and writes an empty string. That is rejected outright by an INT
            // column in strict mode, so every entry would be lost. The literal string
            // 'NULL' is in the driver's exception list and is emitted unquoted.
            $record = array(
                'product_id'    => (int)$product_id,
                'matrix_id'     => ($matrix_id === null) ? 'NULL' : (int)$matrix_id,
                'change'        => (int)$change,
                'stock_after'   => ($stock_after === null) ? 'NULL' : (int)$stock_after,
                'source'        => substr((string)$source, 0, 32),
                'cart_order_id' => empty($cart_order_id) ? 'NULL' : substr((string)$cart_order_id, 0, 18),
                'admin_id'      => self::_adminId(),
                'note'          => substr((string)$note, 0, 255),
                'time'          => time(),
            );
            return (bool)$GLOBALS['db']->insert('CubeCart_stock_log', $record);
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }
    }

    /**
     * Drop entries past the retention window
     *
     * Runs on a small share of writes rather than on a schedule, so the log needs
     * no cron task and no setting. The row limit keeps any single call cheap; the
     * next few writes finish the job on a store with a large backlog.
     */
    private static function _prune()
    {
        if (!function_exists('executionChance') || !executionChance(2)) {
            return;
        }
        try {
            $GLOBALS['db']->delete('CubeCart_stock_log', '`time` < '.(time() - (self::RETENTION_DAYS * 86400)), 500);
        } catch (Exception $e) {
            // Housekeeping must never be the reason a stock movement goes unrecorded.
        } catch (Error $e) {
        }
    }

    /**
     * The admin responsible, when the change came from the control panel.
     *
     * @return int|string the admin id, or the literal 'NULL' for a storefront change
     */
    private static function _adminId()
    {
        if (!defined('CC_IN_ADMIN') || !CC_IN_ADMIN) {
            return 'NULL';
        }
        if (!isset($GLOBALS['session']) || !is_object($GLOBALS['session'])) {
            return 'NULL';
        }
        $id = $GLOBALS['session']->get('admin_id', 'admin_data');
        return (is_numeric($id) && $id > 0) ? (int)$id : 'NULL';
    }
}
