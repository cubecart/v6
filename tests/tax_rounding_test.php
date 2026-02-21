<?php
/**
 * Tax Rounding Bug Investigation & Regression Tests
 *
 * Standalone script demonstrating, and verifying the fix for, two rounding
 * bugs found in CubecaRT's tax/VAT calculation pipeline.
 *
 * Run from the repo root:
 *   php tests/tax_rounding_test.php
 *
 * ===== BUGS FIXED =====
 *
 * BUG 1  tax.class.php – productTax() accumulated the per-line tax amount
 *        AFTER rounding it with sprintf('%.2F',...).  Summing n rounded values
 *        can differ from rounding the sum by up to (n-1)×0.005, causing the
 *        displayed total tax to be 1-2p wrong.
 *        FIX: accumulate the raw (unrounded) float; keep sprintf only for the
 *             per-line return value shown on invoices/display.
 *
 * BUG 2  cart.class.php – for tax-inclusive products, inclusiveTaxRemove()
 *        divides the unit price by (1+rate) producing a float with many
 *        decimal places.  When multiplied by qty and then tax is re-applied,
 *        the reconstructed total can differ from qty×original_price by ±1p
 *        due to IEEE-754 midpoint rounding (e.g. 3×£7.49 inc 20% VAT → £22.48
 *        instead of £22.47).
 *        FIX: after computing the per-line tax (productTax), add
 *             (inc_line − rounded_tax) to _subtotal instead of the imprecise
 *             ex-tax line from the division.  inc_line = original_inc_unit×qty
 *             is computed before inclusiveTaxRemove is called.
 */

$pass = 0;
$fail = 0;

function assert_equals(string $label, string $expected, string $actual): void
{
    global $pass, $fail;
    if ($expected === $actual) {
        echo "  PASS  $label\n";
        $pass++;
    } else {
        echo "  FAIL  $label\n";
        echo "        expected: $expected\n";
        echo "        actual:   $actual\n";
        $fail++;
    }
}

// ---------------------------------------------------------------------------
// Helpers mirroring the FIXED code paths
// ---------------------------------------------------------------------------

/** Fixed productTax – tax-exclusive path (Bug 1 fix). */
function productTax_excl(float $price, float $rate_pct, float &$total_tax_add): string
{
    $raw  = $price * ($rate_pct / 100);   // accumulate RAW
    $total_tax_add += $raw;
    return sprintf('%.2F', $raw);          // return rounded for display
}

/** Fixed productTax – tax-inclusive path (Bug 1 fix). */
function productTax_incl(float $price, float $rate_pct, float &$total_tax_inc): string
{
    $raw  = $price - ($price / (($rate_pct / 100) + 1));
    $total_tax_inc += $raw;
    return sprintf('%.2F', $raw);
}

/** Mirrors inclusiveTaxRemove() – strips VAT from a unit price (no rounding). */
function inclusiveTaxRemove(float $price, float $rate_pct): float
{
    return $price - ($price - ($price / (($rate_pct / 100) + 1)));
}

/**
 * Fixed cart total calculation.
 *
 * For inclusive products (Bug 2 fix): subtotal contribution = inc_line - rounded_tax.
 * For exclusive products: unchanged.
 *
 * @param array $items  Each item: ['price'=>float, 'qty'=>int, 'inclusive'=>bool, 'rate'=>float]
 * @param float $shipping
 * @return array ['subtotal', 'total_tax', 'total']
 */
function cart_totals_fixed(array $items, float $shipping = 0.0): array
{
    $subtotal_raw    = 0.0;
    $total_tax_add   = 0.0;

    foreach ($items as $item) {
        $rate = $item['rate'];
        $qty  = $item['qty'];

        if ($item['inclusive']) {
            // Bug 2 fix: save original inclusive unit price BEFORE stripping
            $inc_unit = $item['price'];
            $ex_unit  = inclusiveTaxRemove($inc_unit, $rate);
            $line_ex  = $ex_unit * $qty;   // the imprecise ex-tax line price

            // productTax called with ex-tax line price, tax_inclusive=false
            $tax_str  = productTax_excl($line_ex, $rate, $total_tax_add);

            // Bug 2 fix: use (inc_line - rounded_tax) not the imprecise $line_ex
            $inc_line = $inc_unit * $qty;
            $subtotal_raw += $inc_line - (float)$tax_str;
        } else {
            $line_ex  = $item['price'] * $qty;
            productTax_excl($line_ex, $rate, $total_tax_add);
            $subtotal_raw += $line_ex;
        }
    }

    $basket_subtotal  = sprintf('%.2F', $subtotal_raw);
    $basket_total_tax = sprintf('%.2F', $total_tax_add);  // Bug 1 fix: raw float rounded once
    $basket_total     = sprintf('%.2F', (float)$basket_subtotal + $shipping + (float)$basket_total_tax);

    return [
        'subtotal'  => $basket_subtotal,
        'total_tax' => $basket_total_tax,
        'total'     => $basket_total,
    ];
}

/**
 * ORIGINAL (unfixed) cart total calculation for comparison.
 */
function cart_totals_original(array $items, float $shipping = 0.0): array
{
    $subtotal_raw    = 0.0;
    $total_tax_add   = 0.0;

    foreach ($items as $item) {
        $rate = $item['rate'];
        $qty  = $item['qty'];

        if ($item['inclusive']) {
            $ex_unit = inclusiveTaxRemove($item['price'], $rate);
            $line_ex = $ex_unit * $qty;
            // Original: accumulate rounded string
            $amount  = sprintf('%.2F', $line_ex * ($rate / 100));
            $total_tax_add += (float)$amount;
            $subtotal_raw  += $line_ex;   // imprecise ex-tax line
        } else {
            $line_ex = $item['price'] * $qty;
            $amount  = sprintf('%.2F', $line_ex * ($rate / 100));
            $total_tax_add += (float)$amount;
            $subtotal_raw  += $line_ex;
        }
    }

    $basket_subtotal  = sprintf('%.2F', $subtotal_raw);
    $basket_total_tax = sprintf('%.2F', $total_tax_add);
    // Original line 1017: uses raw _subtotal (not rounded basket['subtotal'])
    $total_raw        = $subtotal_raw + $shipping + (float)$basket_total_tax;
    $basket_total     = sprintf('%.2F', $total_raw);

    return [
        'subtotal'  => $basket_subtotal,
        'total_tax' => $basket_total_tax,
        'total'     => $basket_total,
    ];
}

// ===========================================================================
echo "=================================================================\n";
echo " BUG 1: Per-line tax rounding accumulation\n";
echo " (sum of rounded-per-line ≠ round of total)\n";
echo "=================================================================\n";

echo "\n--- Test 1a: 3 × £0.33 ex-tax, 20% VAT ---\n";
// Per-line tax = 0.066 → "0.07"; accumulated: 3×0.07=0.21
// Tax on total: 0.99×0.20=0.198 → "0.20"  ← 1p discrepancy in ORIGINAL
{
    $items = [['price'=>0.33,'qty'=>1,'inclusive'=>false,'rate'=>20],
              ['price'=>0.33,'qty'=>1,'inclusive'=>false,'rate'=>20],
              ['price'=>0.33,'qty'=>1,'inclusive'=>false,'rate'=>20]];
    $orig  = cart_totals_original($items);
    $fixed = cart_totals_fixed($items);
    $expected_tax = sprintf('%.2F', 0.99 * 0.20); // "0.20"
    echo "  Expected tax on total: $expected_tax\n";
    echo "  Original tax: {$orig['total_tax']} | Fixed tax: {$fixed['total_tax']}\n";
    assert_equals('Original tax = expected [demonstrates bug]', $expected_tax, $orig['total_tax']);
    assert_equals('Fixed tax = expected',                        $expected_tax, $fixed['total_tax']);
}

echo "\n--- Test 1b: 4 × £0.375 ex-tax, 20% VAT ---\n";
// Per-line tax = 0.075 → "0.08"; accumulated: 4×0.08=0.32
// Tax on total: 1.50×0.20=0.30  ← 2p discrepancy in ORIGINAL
{
    $items = array_fill(0, 4, ['price'=>0.375,'qty'=>1,'inclusive'=>false,'rate'=>20]);
    $orig  = cart_totals_original($items);
    $fixed = cart_totals_fixed($items);
    $expected_tax = sprintf('%.2F', 1.5 * 0.20);
    echo "  Expected tax on total: $expected_tax\n";
    echo "  Original tax: {$orig['total_tax']} | Fixed tax: {$fixed['total_tax']}\n";
    assert_equals('Original tax = expected [demonstrates bug]', $expected_tax, $orig['total_tax']);
    assert_equals('Fixed tax = expected',                        $expected_tax, $fixed['total_tax']);
}

echo "\n--- Test 1c: 3 × £0.34 ex-tax, 20% VAT ---\n";
{
    $items = array_fill(0, 3, ['price'=>0.34,'qty'=>1,'inclusive'=>false,'rate'=>20]);
    $orig  = cart_totals_original($items);
    $fixed = cart_totals_fixed($items);
    $expected_tax = sprintf('%.2F', 1.02 * 0.20);
    echo "  Expected tax on total: $expected_tax\n";
    echo "  Original tax: {$orig['total_tax']} | Fixed tax: {$fixed['total_tax']}\n";
    assert_equals('Original tax = expected [demonstrates bug]', $expected_tax, $orig['total_tax']);
    assert_equals('Fixed tax = expected',                        $expected_tax, $fixed['total_tax']);
}

echo "\n--- Test 1d: Mixed products — £2.49, £3.75, £0.99 ex-tax, 20% VAT ---\n";
{
    $items = [['price'=>2.49,'qty'=>1,'inclusive'=>false,'rate'=>20],
              ['price'=>3.75,'qty'=>1,'inclusive'=>false,'rate'=>20],
              ['price'=>0.99,'qty'=>1,'inclusive'=>false,'rate'=>20]];
    $orig  = cart_totals_original($items);
    $fixed = cart_totals_fixed($items);
    $expected_tax = sprintf('%.2F', 7.23 * 0.20);
    echo "  Expected tax on total (£7.23): $expected_tax\n";
    echo "  Original tax: {$orig['total_tax']} | Fixed tax: {$fixed['total_tax']}\n";
    assert_equals('Original tax = expected', $expected_tax, $orig['total_tax']);
    assert_equals('Fixed tax = expected',    $expected_tax, $fixed['total_tax']);
}

// ===========================================================================
echo "\n=================================================================\n";
echo " BUG 2: Tax-inclusive price floating-point drift\n";
echo " (reconstructed total ≠ qty × original_price)\n";
echo "=================================================================\n";

echo "\n--- Test 2a: 3 × £7.49 inc 20% VAT — classic penny bug ---\n";
{
    $items    = [['price'=>7.49,'qty'=>3,'inclusive'=>true,'rate'=>20]];
    $expected = sprintf('%.2F', 7.49 * 3); // "22.47"
    $orig     = cart_totals_original($items);
    $fixed    = cart_totals_fixed($items);
    echo "  Expected total: £$expected\n";
    echo "  Original: subtotal={$orig['subtotal']} tax={$orig['total_tax']} total={$orig['total']}\n";
    echo "  Fixed:    subtotal={$fixed['subtotal']} tax={$fixed['total_tax']} total={$fixed['total']}\n";
    assert_equals('Original total = £22.47 [demonstrates bug]', $expected, $orig['total']);
    assert_equals('Fixed total = £22.47',                        $expected, $fixed['total']);
    $sum = sprintf('%.2F', (float)$fixed['subtotal'] + (float)$fixed['total_tax']);
    assert_equals('Fixed: subtotal + tax = total',               $fixed['total'], $sum);
}

echo "\n--- Test 2b: 3 × £14.99 inc 20% VAT ---\n";
{
    $items    = [['price'=>14.99,'qty'=>3,'inclusive'=>true,'rate'=>20]];
    $expected = sprintf('%.2F', 14.99 * 3); // "44.97"
    $orig     = cart_totals_original($items);
    $fixed    = cart_totals_fixed($items);
    echo "  Expected total: £$expected\n";
    echo "  Original: subtotal={$orig['subtotal']} tax={$orig['total_tax']} total={$orig['total']}\n";
    echo "  Fixed:    subtotal={$fixed['subtotal']} tax={$fixed['total_tax']} total={$fixed['total']}\n";
    assert_equals('Original total = expected [demonstrates bug]', $expected, $orig['total']);
    assert_equals('Fixed total = expected',                        $expected, $fixed['total']);
    $sum = sprintf('%.2F', (float)$fixed['subtotal'] + (float)$fixed['total_tax']);
    assert_equals('Fixed: subtotal + tax = total',                 $fixed['total'], $sum);
}

echo "\n--- Test 2c: 2 × £4.99 + 3 × £2.49 inc 20% VAT ---\n";
{
    $items    = [['price'=>4.99,'qty'=>2,'inclusive'=>true,'rate'=>20],
                 ['price'=>2.49,'qty'=>3,'inclusive'=>true,'rate'=>20]];
    $expected = sprintf('%.2F', 4.99*2 + 2.49*3); // "17.45"
    $orig     = cart_totals_original($items);
    $fixed    = cart_totals_fixed($items);
    echo "  Expected total: £$expected\n";
    echo "  Original: {$orig['subtotal']} + {$orig['total_tax']} = {$orig['total']}\n";
    echo "  Fixed:    {$fixed['subtotal']} + {$fixed['total_tax']} = {$fixed['total']}\n";
    assert_equals('Original total = expected', $expected, $orig['total']);
    assert_equals('Fixed total = expected',    $expected, $fixed['total']);
}

echo "\n--- Test 2d: 5 × £4.79 inc 20% VAT ---\n";
{
    $items    = [['price'=>4.79,'qty'=>5,'inclusive'=>true,'rate'=>20]];
    $expected = sprintf('%.2F', 4.79 * 5); // "23.95"
    $orig     = cart_totals_original($items);
    $fixed    = cart_totals_fixed($items);
    echo "  Expected total: £$expected\n";
    echo "  Original: {$orig['subtotal']} + {$orig['total_tax']} = {$orig['total']}\n";
    echo "  Fixed:    {$fixed['subtotal']} + {$fixed['total_tax']} = {$fixed['total']}\n";
    assert_equals('Original total = expected', $expected, $orig['total']);
    assert_equals('Fixed total = expected',    $expected, $fixed['total']);
    $sum = sprintf('%.2F', (float)$fixed['subtotal'] + (float)$fixed['total_tax']);
    assert_equals('Fixed: subtotal + tax = total', $fixed['total'], $sum);
}

echo "\n--- Test 2e: Mixed inclusive + exclusive products ---\n";
// £9.99 inc 20% VAT + £5.00 ex-tax 20% VAT + shipping £2.95
{
    $items    = [['price'=>9.99,'qty'=>1,'inclusive'=>true,'rate'=>20],
                 ['price'=>5.00,'qty'=>1,'inclusive'=>false,'rate'=>20]];
    $shipping = 2.95;
    // Expected: total_inc = 9.99, total_ex = 5.00, tax_ex = 1.00
    // total = 9.99 + 5.00 + 1.00 + 2.95 = 18.94
    $expected = sprintf('%.2F', 9.99 + 5.00 + 1.00 + 2.95);
    $fixed    = cart_totals_fixed($items, $shipping);
    echo "  Expected total: £$expected\n";
    echo "  Fixed: {$fixed['subtotal']} + {$fixed['total_tax']} + $shipping = {$fixed['total']}\n";
    assert_equals('Fixed total = expected', $expected, $fixed['total']);
    $sum = sprintf('%.2F', (float)$fixed['subtotal'] + $shipping + (float)$fixed['total_tax']);
    assert_equals('Fixed: subtotal + shipping + tax = total', $fixed['total'], $sum);
}

// ===========================================================================
echo "\n=================================================================\n";
echo " CONSISTENCY: displayed total always equals subtotal + shipping + tax\n";
echo "=================================================================\n";

$consistency_cases = [
    ['items' => [['price'=>10.00,'qty'=>1,'inclusive'=>false,'rate'=>20]],   'ship'=>0.00, 'label'=>'£10 ex-tax 20%'],
    ['items' => [['price'=>9.99, 'qty'=>2,'inclusive'=>true, 'rate'=>20]],   'ship'=>0.00, 'label'=>'2×£9.99 inc 20%'],
    ['items' => [['price'=>7.49, 'qty'=>3,'inclusive'=>true, 'rate'=>20]],   'ship'=>3.99, 'label'=>'3×£7.49 inc 20% + shipping'],
    ['items' => [['price'=>0.33, 'qty'=>7,'inclusive'=>false,'rate'=>20]],   'ship'=>0.00, 'label'=>'7×£0.33 ex-tax 20%'],
    ['items' => [['price'=>12.00,'qty'=>1,'inclusive'=>false,'rate'=>20],
                 ['price'=>3.49, 'qty'=>2,'inclusive'=>true, 'rate'=>20]],   'ship'=>4.95, 'label'=>'Mixed + shipping'],
];

foreach ($consistency_cases as $case) {
    $r    = cart_totals_fixed($case['items'], $case['ship']);
    $ship = sprintf('%.2F', $case['ship']);
    $sum  = sprintf('%.2F', (float)$r['subtotal'] + (float)$ship + (float)$r['total_tax']);
    assert_equals(
        "Consistency — {$case['label']}: subtotal({$r['subtotal']}) + ship($ship) + tax({$r['total_tax']}) = total",
        $r['total'],
        $sum
    );
}

// ===========================================================================
echo "\n=================================================================\n";
echo " SUMMARY\n";
echo "=================================================================\n";
$total = $pass + $fail;
echo "\n  Passed: $pass / $total\n";
echo "  Failed: $fail / $total\n";
if ($fail > 0) {
    echo "\n  BUGS REMAIN: $fail test(s) failed.\n";
} else {
    echo "\n  All tests passed — rounding fixes verified.\n";
}
echo "\n";
