<?php
/**
 * #4130 — Backfill CubeCart_order_summary.basket compression for historic
 * non-Pending orders. Compressed rows start with the zlib magic byte 0x78;
 * raw PHP serialize() output never does, so the sniff is unambiguous.
 *
 * Pending (status 1) orders are intentionally left uncompressed — they are
 * re-written frequently during payment retries and the helpers will compress
 * them on the status transition out of Pending.
 */

$batch_size = 500;
$total_processed = 0;
$total_failed = 0;

do {
    $rows = $db->misc(
        "SELECT `cart_order_id`, `basket` FROM `".$glob['dbprefix']."CubeCart_order_summary`
         WHERE `status` > 1
           AND `basket` IS NOT NULL
           AND LENGTH(`basket`) > 0
           AND LEFT(`basket`, 1) != 0x78
         LIMIT ".(int)$batch_size,
        false,
        false
    );

    if (!is_array($rows) || empty($rows)) {
        break;
    }

    foreach ($rows as $row) {
        $oid = $db->sqlSafe($row['cart_order_id'], true);
        $basket = @unserialize($row['basket'], array('allowed_classes' => false));
        if (!is_array($basket)) {
            $total_failed++;
            // Mark the row so the next iteration's WHERE skips it (else we'd loop forever).
            $db->misc("UPDATE `".$glob['dbprefix']."CubeCart_order_summary` SET `basket` = NULL WHERE `cart_order_id` = {$oid}");
            continue;
        }
        // Write via UNHEX() to avoid Database::sqlSafe() stripslashes() corruption of binary data.
        $hex = bin2hex(gzcompress(serialize($basket), 6));
        $db->misc("UPDATE `".$glob['dbprefix']."CubeCart_order_summary` SET `basket` = UNHEX('{$hex}') WHERE `cart_order_id` = {$oid}");
        $total_processed++;
    }
} while (count($rows) === $batch_size);

if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
    $GLOBALS['cache']->clear();
}

// #4152 — Import the three new email templates for the EU withdrawal feature
// across every shipped language. importEmail() is safe to re-run: it only
// inserts rows that don't already exist, so merchant-edited copies survive.
$new_withdrawal_emails = array(
    'cart.withdrawal_acknowledgment',
    'cart.withdrawal_decision',
    'admin.withdrawal_received',
);
if (is_array($languages)) {
    foreach ($languages as $code => $lang) {
        foreach ($new_withdrawal_emails as $tpl) {
            $language->importEmail('email_'.$code.'.xml', CC_LANGUAGE_DIR, $tpl);
        }
    }
}
