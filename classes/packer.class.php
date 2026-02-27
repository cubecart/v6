<?php
/**
 * CubeCart v6
 * ========================================
 * CubeCart is a registered trade mark of CubeCart Limited
 * Copyright CubeCart Limited 2025. All rights reserved.
 * UK Private Limited Company No. 5323904
 * ========================================
 * Web:   https://www.cubecart.com
 * Email:  hello@cubecart.com
 * License:  GPL-3.0 https://www.gnu.org/licenses/quick-guide-gplv3.html
 */

/**
 * 3-D bin packer for shipping rate calculation.
 *
 * Algorithm: First Fit Decreasing (FFD) with Extreme Point (EP) placement.
 *
 * Items are sorted by volume descending (largest first). Each item is placed
 * in the first open box where it fits, trying all six orientations at every
 * extreme point in bottom-front-left order. When no open box accepts the item
 * the smallest qualifying box from the store's packaging list is opened.
 *
 * Usage:
 *   $packer = new Packer();
 *   $packer->addItem(10, 8, 4, 0.5, 2, 'Widget'); // l, w, h, weight, qty, name
 *   $packer->addItem(5,  5, 5, 0.3, 1, 'Cube');
 *   $result = $packer->pack();
 *   // $result[0]['box']    => ['name'=>'Small','l'=>…,'w'=>…,'h'=>…]
 *   // $result[0]['weight'] => 1.3  (total weight in this box)
 *   // $result[0]['used_volume'] / $result[0]['box_volume'] => fill ratio
 *   echo $packer->getBoxCount(); // number of boxes needed
 */
class Packer
{
    /** @var array Box definitions sorted by volume ascending */
    private $_box_defs = array();

    /** @var array Items waiting to be packed */
    private $_items = array();

    /** @var PackerBox[] Open (in-progress) boxes */
    private $_open = array();

    /** @var array Items too large for any available box */
    private $_overflow = array();

    // =========================================================== Constructor

    public function __construct()
    {
        $defs = $GLOBALS['config']->get('config', 'packaging_boxes');
        if (is_array($defs) && !empty($defs)) {
            usort($defs, function ($a, $b) {
                $va = (float)$a['l'] * (float)$a['w'] * (float)$a['h'];
                $vb = (float)$b['l'] * (float)$b['w'] * (float)$b['h'];
                return $va <=> $vb;
            });
            $this->_box_defs = $defs;
        }
    }

    // ============================================================== Public API

    /**
     * Add one or more identical items to pack.
     *
     * @param float  $l      Length
     * @param float  $w      Width
     * @param float  $h      Height
     * @param float  $weight Item weight
     * @param int    $qty    Number of this item (each packed individually)
     * @param string $name   Optional label (visible in result for inspection)
     */
    public function addItem($l, $w, $h, $weight = 0.0, $qty = 1, $name = '')
    {
        for ($i = 0; $i < (int)$qty; $i++) {
            $this->_items[] = array(
                'l'      => (float)$l,
                'w'      => (float)$w,
                'h'      => (float)$h,
                'weight' => (float)$weight,
                'name'   => (string)$name,
            );
        }
    }

    /**
     * Run the packing algorithm.
     *
     * @return array  Each entry: ['box'=>def, 'items'=>[...], 'weight'=>float,
     *                             'used_volume'=>float, 'box_volume'=>float]
     */
    public function pack()
    {
        $this->_open     = array();
        $this->_overflow = array();

        if (empty($this->_items) || empty($this->_box_defs)) {
            return array();
        }

        // Largest volume first — FFD heuristic gives better packing
        $items = $this->_items;
        usort($items, function ($a, $b) {
            return ($b['l'] * $b['w'] * $b['h']) <=> ($a['l'] * $a['w'] * $a['h']);
        });

        foreach ($items as $item) {
            if (!$this->_fitExisting($item) && !$this->_fitNewBox($item)) {
                $this->_overflow[] = $item;
            }
        }

        return $this->getPacked();
    }

    /**
     * Return packed box results (same structure as pack()).
     *
     * @return array
     */
    public function getPacked()
    {
        $out = array();
        foreach ($this->_open as $box) {
            $out[] = $box->result();
        }
        return $out;
    }

    /**
     * Number of boxes needed after pack().
     *
     * @return int
     */
    public function getBoxCount()
    {
        return count($this->_open);
    }

    /**
     * Items that exceeded every available box size.
     *
     * @return array
     */
    public function getOverflow()
    {
        return $this->_overflow;
    }

    // ============================================================= Private

    /**
     * Try to place $item in one of the already-open boxes.
     */
    private function _fitExisting(array $item)
    {
        foreach ($this->_open as $box) {
            if ($box->tryPack($item)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Open the smallest box definition that fits $item, pack the item into it.
     */
    private function _fitNewBox(array $item)
    {
        foreach ($this->_box_defs as $def) {
            if ($this->_itemFitsDefinition($item, $def)) {
                $box = new PackerBox($def);
                $box->tryPack($item);
                $this->_open[] = $box;
                return true;
            }
        }
        return false;
    }

    /**
     * Return true if $item can fit (in at least one rotation) within $def.
     * Uses sorted-dimension comparison to avoid checking all rotations explicitly.
     */
    private function _itemFitsDefinition(array $item, array $def)
    {
        $id = array((float)$item['l'], (float)$item['w'], (float)$item['h']);
        $bd = array((float)$def['l'],  (float)$def['w'],  (float)$def['h']);
        sort($id);
        sort($bd);
        return $id[0] <= $bd[0] && $id[1] <= $bd[1] && $id[2] <= $bd[2];
    }
}


// =============================================================================

/**
 * Represents one box being filled.
 *
 * Tracks placed items and free space via the Extreme Point method:
 *   - Box starts with one extreme point (EP) at the origin {0,0,0}.
 *   - After each successful placement three new EPs are generated at the
 *     three far faces of the item (+l, +w, +h faces).
 *   - EPs are tried in ascending z→y→x order so items settle toward the
 *     bottom-front-left corner first.
 *   - Placement is accepted only when the item stays within the box walls
 *     and does not overlap any previously placed item.
 *
 * @internal  Used exclusively by Packer.
 */
class PackerBox
{
    const EPS = 1e-6;

    /** @var array Original box definition */
    private $_def;

    /** @var float Box inner dimensions */
    private $_L, $_W, $_H;

    /** @var array Placed items: each entry has x,y,z,l,w,h,item keys */
    private $_placed = array();

    /** @var float Accumulated weight of packed items */
    private $_weight = 0.0;

    /** @var float Accumulated volume of packed items */
    private $_usedVol = 0.0;

    /** @var array Extreme points: each entry has x,y,z keys */
    private $_eps = array();

    // =========================================================== Constructor

    public function __construct(array $def)
    {
        $this->_def = $def;
        $this->_L   = (float)$def['l'];
        $this->_W   = (float)$def['w'];
        $this->_H   = (float)$def['h'];
        $this->_eps = array(array('x' => 0.0, 'y' => 0.0, 'z' => 0.0));
    }

    // ============================================================== Public

    /**
     * Try to place $item somewhere in this box.
     * Attempts all six orientations at every extreme point.
     *
     * @return bool  True if the item was placed successfully.
     */
    public function tryPack(array $item)
    {
        $rotations = $this->_rotations($item);
        $eps       = $this->_sortedEps();

        foreach ($eps as $ep) {
            foreach ($rotations as $r) {
                if ($this->_fits($ep['x'], $ep['y'], $ep['z'],
                                 $r['l'],  $r['w'],  $r['h'])) {
                    $this->_place($ep['x'], $ep['y'], $ep['z'],
                                  $r['l'],  $r['w'],  $r['h'], $item);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Return a summary array for the packed box.
     *
     * @return array
     */
    public function result()
    {
        return array(
            'box'         => $this->_def,
            'items'       => $this->_placed,
            'weight'      => $this->_weight,
            'used_volume' => $this->_usedVol,
            'box_volume'  => $this->_L * $this->_W * $this->_H,
        );
    }

    // ============================================================= Private

    /**
     * Return all unique orientations of $item, sorted by ascending height.
     * Sorting by height prefers flatter orientations, leaving more vertical
     * clearance for subsequent items.
     */
    private function _rotations(array $item)
    {
        $l = (float)$item['l'];
        $w = (float)$item['w'];
        $h = (float)$item['h'];

        $all = array(
            array('l' => $l, 'w' => $w, 'h' => $h),
            array('l' => $l, 'w' => $h, 'h' => $w),
            array('l' => $w, 'w' => $l, 'h' => $h),
            array('l' => $w, 'w' => $h, 'h' => $l),
            array('l' => $h, 'w' => $l, 'h' => $w),
            array('l' => $h, 'w' => $w, 'h' => $l),
        );

        $seen   = array();
        $unique = array();
        foreach ($all as $r) {
            $k = $r['l'] . ',' . $r['w'] . ',' . $r['h'];
            if (!isset($seen[$k])) {
                $seen[$k] = true;
                $unique[] = $r;
            }
        }

        usort($unique, function ($a, $b) {
            return $a['h'] <=> $b['h'];
        });

        return $unique;
    }

    /**
     * Return extreme points sorted bottom (z) → front (y) → left (x).
     * This greedy order makes items settle toward the floor first.
     */
    private function _sortedEps()
    {
        $eps = $this->_eps;
        usort($eps, function ($a, $b) {
            if (abs($a['z'] - $b['z']) > self::EPS) return $a['z'] <=> $b['z'];
            if (abs($a['y'] - $b['y']) > self::EPS) return $a['y'] <=> $b['y'];
            return $a['x'] <=> $b['x'];
        });
        return $eps;
    }

    /**
     * Return true if an item of dimensions ($l×$w×$h) placed at ($x,$y,$z):
     *   (a) stays within the box walls, and
     *   (b) does not overlap any previously placed item.
     */
    private function _fits($x, $y, $z, $l, $w, $h)
    {
        if ($x + $l > $this->_L + self::EPS) return false;
        if ($y + $w > $this->_W + self::EPS) return false;
        if ($z + $h > $this->_H + self::EPS) return false;

        foreach ($this->_placed as $p) {
            if ($x      < $p['x'] + $p['l'] - self::EPS &&
                $p['x'] < $x      + $l      - self::EPS &&
                $y      < $p['y'] + $p['w'] - self::EPS &&
                $p['y'] < $y      + $w      - self::EPS &&
                $z      < $p['z'] + $p['h'] - self::EPS &&
                $p['z'] < $z      + $h      - self::EPS) {
                return false;
            }
        }
        return true;
    }

    /**
     * Record the placement and extend the extreme-point set with the
     * three new points generated at the far faces of the placed item.
     */
    private function _place($x, $y, $z, $l, $w, $h, array $item)
    {
        $this->_placed[] = array(
            'x'    => $x, 'y' => $y, 'z' => $z,
            'l'    => $l, 'w' => $w, 'h' => $h,
            'item' => $item,
        );
        $this->_weight   += (float)($item['weight'] ?? 0.0);
        $this->_usedVol  += $l * $w * $h;

        $candidates = array(
            array('x' => $x + $l, 'y' => $y,     'z' => $z    ),
            array('x' => $x,      'y' => $y + $w, 'z' => $z    ),
            array('x' => $x,      'y' => $y,      'z' => $z + $h),
        );

        foreach ($candidates as $ep) {
            if ($ep['x'] <= $this->_L + self::EPS &&
                $ep['y'] <= $this->_W + self::EPS &&
                $ep['z'] <= $this->_H + self::EPS) {
                $this->_eps[] = $ep;
            }
        }
    }
}
