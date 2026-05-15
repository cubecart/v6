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
        $basket = @unserialize($row['basket'], array('allowed_classes' => false));
        if (!is_array($basket)) {
            $total_failed++;
            // Mark the row so the next iteration's WHERE skips it (else we'd loop forever).
            // We can't write a sentinel to the basket column itself without obscuring the
            // original data, so set it to NULL to drop it from the candidate set. The row
            // remains otherwise intact and the helper treats NULL as "empty basket".
            $db->update(
                'CubeCart_order_summary',
                array('basket' => null),
                array('cart_order_id' => $row['cart_order_id'])
            );
            continue;
        }

        $db->update(
            'CubeCart_order_summary',
            array('basket' => gzcompress(serialize($basket), 6)),
            array('cart_order_id' => $row['cart_order_id'])
        );
        $total_processed++;
    }
} while (count($rows) === $batch_size);

if (isset($GLOBALS['cache']) && is_object($GLOBALS['cache'])) {
    $GLOBALS['cache']->clear();
}
