<?php
/**
 * cf-thumbs.php - on-the-fly thumbnails, cached at Cloudflare. Added 2026-08-17.
 *
 * With block_thumbs on, CubeCart still emits /images/cache/<name>.<size>.<ext>
 * URLs but never writes the file. images/.htaccess routes the miss here; we
 * resize from images/source using CubeCart's own GD steps and stream it. Nothing
 * is written to disk. Cloudflare caches at the edge, so a given image is resized
 * about once per edge TTL instead of living in images/cache forever.
 *
 * ENABLE (off until both are done)
 *   1. includes/global.inc.php:  $glob['block_thumbs'] = 1;
 *      $glob overrides the DB and is never written back, so an admin saving Store
 *      Settings cannot undo it. The DB works too, but is less durable.
 *   2. Rename images/.cf-thumbs.htaccess to .htaccess, or paste its block into the
 *      existing one. Subfolder installs need the path edited; see that file.
 *
 * Cloudflare itself needs no setup: it caches these extensions by default, and the
 * Cloudflare-CDN-Cache-Control header below sets the edge TTL. Only intervene if
 * the zone already has a rule bypassing cache for /images/.
 *
 * CHECK
 *   curl -sI https://store/images/cache/foo.200.jpg | grep -i 'x-thumb\|cf-cache'
 *     X-Thumb: generated | not-modified | source-fallback | fail-404 | fail-503
 *     X-Thumb-Warning: block_thumbs-not-set   step 1 has not taken effect
 *   CF-Cache-Status should be HIT on the second request for the same URL.
 *
 * NOTES
 *   - Replacing an image under the same filename needs a Cloudflare purge; the
 *     URL does not change. Equally true of the old on-disk cache.
 *   - Only sizes the active skin declares are served, which also stops a bot
 *     requesting thousands of arbitrary sizes. After changing a skin's sizes,
 *     delete cache/cf-thumbs-profile.json.
 *   - Revert: undo steps 1 and 2, then purge the Cloudflare cache.
 */

// --- tunables ---------------------------------------------------------------

// CubeCart's GD auto-rotates from EXIF Orientation. On this store that is wrong:
// 147 of 12,234 sources claim Orientation=6/8 while their pixels (and burnt-in
// watermark) are already upright, so honouring it lays them on their side. Set
// true only if the sources are ever re-scanned with trustworthy EXIF.
const CFTHUMBS_APPLY_EXIF_ROTATION = false;

// Refuse to resize for requests that did not come through Cloudflare. A miss
// costs a GD resize and one category page asks for dozens at once, so an
// unproxied crawler can pin the CPU. No CF-Ray means the proxy is paused,
// bypassed or DNS-only. The .htaccess rule checks this too; this is the backstop.
// Unproxied thumbnails 404 by design: recoverable, unlike an origin under load.
const CFTHUMBS_REQUIRE_CLOUDFLARE = true;

const CFTHUMBS_BROWSER_TTL  = 604800;    // 7 days, as the old static files behaved
const CFTHUMBS_EDGE_TTL     = 2592000;   // 30 days; longer than browser TTL so the edge absorbs repeats
const CFTHUMBS_ERROR_TTL    = 300;       // cache errors briefly so bad URLs cannot hammer PHP
const CFTHUMBS_MAX_PIXELS   = 50000000;  // refuse absurd sources so GD cannot exhaust memory
const CFTHUMBS_MAX_PASSTHRU = 5242880;   // largest source we will serve whole as a fallback
const CFTHUMBS_PROFILE_TTL  = 3600;      // how long to memoise the skin and its sizes
const CFTHUMBS_PROFILE_FILE = '/cache/cf-thumbs-profile.json';

// --- responses ---------------------------------------------------------------

// One place for the cache headers so the three exit paths cannot drift apart.
function cfthumbs_cache_headers($etag)
{
    header('Cache-Control: public, max-age='.CFTHUMBS_BROWSER_TTL);
    header('Cloudflare-CDN-Cache-Control: public, max-age='.CFTHUMBS_EDGE_TTL);
    header('ETag: '.$etag);
}

// Short-lived error. Emits no cookie, so Cloudflare is free to cache it.
function cfthumbs_fail($code)
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    header('Cache-Control: public, max-age='.CFTHUMBS_ERROR_TTL);
    header('X-Thumb: fail-'.$code);
    echo ($code === 404) ? "Not found\n" : "Thumbnail unavailable\n";
    exit;
}

function cfthumbs_send($data, $ctype, $etag, $mtime)
{
    header('Content-Type: '.$ctype);
    header('Content-Length: '.strlen($data));
    cfthumbs_cache_headers($etag);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
    header('X-Content-Type-Options: nosniff');
    header('X-Thumb: generated');
    echo $data;
    exit;
}

// Serve the original unchanged, as imagePath() does when it cannot make a thumb.
function cfthumbs_passthru($file, $ctype, $etag, $mtime)
{
    if (filesize($file) > CFTHUMBS_MAX_PASSTHRU) {
        cfthumbs_fail(404);
    }
    header('Content-Type: '.$ctype);
    header('Content-Length: '.filesize($file));
    cfthumbs_cache_headers($etag);
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
    header('X-Content-Type-Options: nosniff');
    header('X-Thumb: source-fallback');
    readfile($file);
    exit;
}

// --- 1. what was asked for ---------------------------------------------------

/**
 * foo.jpg.200.jpg -> stem 'foo.jpg', size 200, ext 'jpg'  (source foo.jpg.jpg)
 * bar.200.jpg     -> stem 'bar',     size 200, ext 'jpg'  (source bar.jpg)
 *
 * The greedy .+ is the exact inverse of imagePath()'s sprintf('%s.%d%s').
 */
function cfthumbs_parse_request()
{
    $uri  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path = rawurldecode((string)parse_url($uri, PHP_URL_PATH));

    if (strpos($path, "\0") !== false || !preg_match('#/images/cache/(.+)$#', $path, $hit)) {
        cfthumbs_fail(404);
    }
    $relative = $hit[1];

    // Fail fast on traversal; the realpath check in locate_source is the real guard.
    if (strpos($relative, '..') !== false || strpos($relative, '\\') !== false) {
        cfthumbs_fail(404);
    }

    if (!preg_match('#^(.+)\.([0-9]+)\.(jpe?g|png|gif|webp)$#i', $relative, $parts)) {
        cfthumbs_fail(404);
    }

    return array(
        'relative' => $relative,
        'stem'     => $parts[1],
        'size'     => (int)$parts[2],
        'ext'      => $parts[3],
    );
}

// --- 2. find the source ------------------------------------------------------

// Returns an absolute path, or 404s if it resolves outside images/source|skins.
function cfthumbs_locate_source($root, $stem, $ext)
{
    // Skin placeholders (noimage.png etc) live under skins/, everything else is
    // a real upload under images/source.
    if (strpos($stem, 'skins/') === 0) {
        $source = $root.'/'.$stem.'.'.$ext;
    } else {
        $source = $root.'/images/source/'.$stem.'.'.$ext;
    }

    $source = realpath($source);
    if ($source === false || !is_file($source)) {
        cfthumbs_fail(404);
    }

    foreach (array($root.'/images/source', $root.'/skins') as $allowed) {
        $allowed = realpath($allowed);
        if ($allowed !== false && strpos($source, $allowed.DIRECTORY_SEPARATOR) === 0) {
            return $source;
        }
    }
    cfthumbs_fail(404);
}

// --- 3. which sizes may we serve? --------------------------------------------

/**
 * array('skin' => string, 'sizes' => array(size => quality), 'blocked' => bool)
 * Memoised to disk so a miss does not normally cost a database connection.
 */
function cfthumbs_profile($root)
{
    $memo = $root.CFTHUMBS_PROFILE_FILE;

    if (is_file($memo) && (time() - filemtime($memo)) < CFTHUMBS_PROFILE_TTL) {
        $data = json_decode((string)file_get_contents($memo), true);
        if (is_array($data) && !empty($data['sizes'])) {
            return $data;
        }
    }

    $data = cfthumbs_build_profile($root);
    if ($data !== null) {
        @file_put_contents($memo, json_encode($data), LOCK_EX);
        return $data;
    }

    // DB or skin unreadable: prefer a stale memo over breaking every image.
    if (is_file($memo)) {
        $data = json_decode((string)file_get_contents($memo), true);
        if (is_array($data) && !empty($data['sizes'])) {
            return $data;
        }
    }
    return null;
}

// skin_folder from the store config, then the sizes that skin declares.
function cfthumbs_build_profile($root)
{
    $globals_file = $root.'/includes/global.inc.php';
    if (!is_file($globals_file)) {
        return null;
    }
    $glob = array();
    include $globals_file;
    if (empty($glob['dbhost']) || empty($glob['dbdatabase'])) {
        return null;
    }

    $skin    = null;
    $blocked = false;
    try {
        mysqli_report(MYSQLI_REPORT_OFF);
        $db = @new mysqli($glob['dbhost'], $glob['dbusername'], $glob['dbpassword'], $glob['dbdatabase']);
        if (!$db->connect_errno) {
            $prefix = isset($glob['dbprefix']) ? $glob['dbprefix'] : '';
            // block_thumbs rides along in the same round trip, only to drive the warning.
            $sql = "SELECT config_key, config_value FROM `".$prefix."CubeCart_config`"
                 . " WHERE name='config' AND config_key IN ('skin_folder','block_thumbs')";
            if ($res = $db->query($sql)) {
                while ($row = $res->fetch_row()) {
                    if ($row[0] === 'skin_folder') {
                        $skin = $row[1];
                    } elseif ($row[0] === 'block_thumbs') {
                        $blocked = !empty($row[1]);
                    }
                }
            }
            $db->close();
        }
    } catch (Throwable $e) {
        return null;
    }
    if (empty($skin)) {
        return null;
    }

    $xml_file = $root.'/skins/'.basename($skin).'/config.xml';
    if (!is_file($xml_file)) {
        return null;
    }
    $xml = @simplexml_load_file($xml_file);
    if ($xml === false || !isset($xml->images->image)) {
        return null;
    }

    $sizes = array();
    foreach ($xml->images->image as $image) {
        $max = (int)$image['maximum'];
        if ($max > 0) {
            $quality = (int)$image['quality'];
            $sizes[$max] = ($quality > 0) ? $quality : 80;
        }
    }
    return $sizes ? array('skin' => $skin, 'sizes' => $sizes, 'blocked' => $blocked) : null;
}

// $glob overrides the DB, so a global.inc.php setting counts as set. Read as
// text: including the file twice would redeclare its constants.
function cfthumbs_block_thumbs_in_globals($root)
{
    $file = $root.'/includes/global.inc.php';
    if (!is_file($file)) {
        return false;
    }
    return (bool)preg_match(
        '#\$glob\s*\[\s*[\x27"]block_thumbs[\x27"]\s*\]\s*=\s*[\x27"]?[1-9]#',
        (string)file_get_contents($file)
    );
}

// --- 4. resize ---------------------------------------------------------------

/**
 * Mirrors GD::gdResize() and GD::gdSave() step for step, so output is
 * byte-identical to the thumbnails CubeCart used to write. The one deliberate
 * omission is gdOrientate(); see CFTHUMBS_APPLY_EXIF_ROTATION.
 *
 * @return string|false
 */
function cfthumbs_render($source, $type, $size, $quality)
{
    switch ($type) {
        case IMAGETYPE_GIF:
            $im = @imagecreatefromgif($source);
            break;
        case IMAGETYPE_JPEG:
            cfthumbs_reserve_memory($source);
            $im = @imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $im = @imagecreatefrompng($source);
            if ($im) { imagesavealpha($im, true); }
            break;
        case IMAGETYPE_WEBP:
            $im = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false;
            break;
        default:
            return false;
    }
    if (!$im) {
        return false;
    }

    $width  = imagesx($im);
    $height = imagesy($im);

    if ($width <= $size && $height <= $size) {
        $out = $im;                                     // already small enough
    } else {
        $x_ratio = $size / $width;
        $y_ratio = $size / $height;
        if (($x_ratio * $height) < $size) {
            $out_width  = $size;
            $out_height = ceil($x_ratio * $height);
        } else {
            $out_width  = ceil($y_ratio * $width);
            $out_height = $size;
        }

        $out = imagecreatetruecolor($out_width, $out_height);
        if ($type == IMAGETYPE_GIF) {
            $transparent = imagecolortransparent($im);
            if ($transparent >= 0) {
                $colour = imagecolorsforindex($im, $transparent);
                $new    = imagecolorallocate($out, $colour['red'], $colour['green'], $colour['blue']);
                imagefill($out, 0, 0, $new);
                imagecolortransparent($out, $new);
            }
        } else {
            imagealphablending($out, false);
            imagesavealpha($out, true);
        }
        imagecopyresampled($out, $im, 0, 0, 0, 0, $out_width, $out_height, $width, $height);
        imagedestroy($im);
    }

    if (CFTHUMBS_APPLY_EXIF_ROTATION && $type == IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($source);
        if (is_array($exif) && !empty($exif['Orientation'])) {
            $angle = array(3 => 180, 6 => -90, 8 => 90);
            if (isset($angle[$exif['Orientation']])) {
                $rotated = imagerotate($out, $angle[$exif['Orientation']], 0);
                if ($rotated) { imagedestroy($out); $out = $rotated; }
            }
        }
    }

    imageinterlace($out, true);

    ob_start();
    switch ($type) {
        case IMAGETYPE_GIF:
            $ok = imagegif($out);
            break;
        case IMAGETYPE_JPEG:
            $ok = imagejpeg($out, null, $quality);
            break;
        case IMAGETYPE_PNG:
            imagesavealpha($out, true);
            $ok = imagepng($out);
            break;
        case IMAGETYPE_WEBP:
            $ok = function_exists('imagewebp') ? imagewebp($out, null, $quality) : false;
            break;
        default:
            $ok = false;
    }
    $data = ob_get_clean();
    imagedestroy($out);

    return ($ok && $data !== '') ? $data : false;
}

// Raise memory_limit to fit the decode, matching GD::_allocateMemory(). On
// failure the decode simply fails and we take the source-fallback path.
function cfthumbs_reserve_memory($source)
{
    $limit = ini_get('memory_limit');
    if ($limit == -1) {
        return true;
    }
    $suffix = strtoupper(substr($limit, -1));
    if ($suffix === 'G') {
        $limit = (int)$limit * 1024;
    } elseif ($suffix === 'K') {
        $limit = (int)$limit / 1024;
    } else {
        $limit = (int)$limit;
    }

    $data     = @getimagesize($source);
    $bits     = isset($data['bits']) ? $data['bits'] : 8;
    $channels = isset($data['channels']) ? $data['channels'] : 4;
    $needed   = round(($data[0] * $data[1] * $bits * $channels / 8 + pow(2, 16)) * 1.65);

    if (memory_get_usage() + $needed > $limit * pow(1024, 2)) {
        $raised = $limit + ceil(((memory_get_usage() + $needed) - $limit * pow(1024, 2)) / pow(1024, 2));
        return (bool)@ini_set('memory_limit', $raised.'M');
    }
    return true;
}

// --- main --------------------------------------------------------------------

function cfthumbs_main($root)
{
    if (CFTHUMBS_REQUIRE_CLOUDFLARE && empty($_SERVER['HTTP_CF_RAY'])) {
        cfthumbs_fail(404);
    }

    $request = cfthumbs_parse_request();
    $source  = cfthumbs_locate_source($root, $request['stem'], $request['ext']);

    $profile = cfthumbs_profile($root);
    if ($profile === null) {
        cfthumbs_fail(503);
    }
    if (!isset($profile['sizes'][$request['size']])) {
        cfthumbs_fail(404);
    }
    $quality = (int)$profile['sizes'][$request['size']];

    // Without block_thumbs, CubeCart is still filling images/cache as pages
    // render and we only see the window after the cache is emptied.
    if (empty($profile['blocked']) && !cfthumbs_block_thumbs_in_globals($root)) {
        header('X-Thumb-Warning: block_thumbs-not-set');
    }

    $info = @getimagesize($source);
    if ($info === false || ($info[0] * $info[1]) > CFTHUMBS_MAX_PIXELS) {
        cfthumbs_fail(404);
    }
    $ctype = image_type_to_mime_type($info[2]);
    $mtime = filemtime($source);
    $etag  = '"'.md5($request['relative'].'|'.$mtime.'|'.filesize($source).'|'.$request['size'].'|'.$quality).'"';

    $if_none_match = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : '';
    $if_modified   = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) : false;

    if ($if_none_match === $etag || ($if_modified !== false && $if_modified >= $mtime)) {
        http_response_code(304);
        cfthumbs_cache_headers($etag);
        header('X-Thumb: not-modified');
        exit;
    }

    $data = cfthumbs_render($source, $info[2], $request['size'], $quality);
    if ($data === false) {
        cfthumbs_passthru($source, $ctype, $etag, $mtime);   // rather than a broken image
    }
    cfthumbs_send($data, $ctype, $etag, $mtime);
}

set_time_limit(30);
ini_set('display_errors', '0');

cfthumbs_main(__DIR__);
