<?php
/**
 * Migrate CubeCart_config from base64-JSON blob storage to NVP (Name-Value Pair) rows.
 * The SQL upgrade has already renamed the old table to CubeCart_config_legacy
 * and created the new CubeCart_config table with (name, config_key, config_value).
 */

$legacy_rows = $db->select('CubeCart_config_legacy', false, false);
if ($legacy_rows) {
    foreach ($legacy_rows as $legacy) {
        $name = $legacy['name'];
        $raw  = $legacy['array'];

        // Decode: handle legacy serialize format and base64-JSON
        if (preg_match('/^a:[0-9]/', $raw)) {
            $data = unserialize($raw);
        } else {
            $data = json_decode(base64_decode($raw), true);
        }

        if (!is_array($data) || empty($data)) {
            continue;
        }

        // Decode the double-base64 fields that were encoded within the JSON
        if (isset($data['offline_content']) && !empty($data['offline_content'])) {
            $decoded = base64_decode($data['offline_content'], true);
            if ($decoded !== false) {
                $data['offline_content'] = $decoded;
            }
        }
        if (isset($data['store_copyright']) && !empty($data['store_copyright'])) {
            $decoded = base64_decode($data['store_copyright'], true);
            if ($decoded !== false) {
                $data['store_copyright'] = $decoded;
            }
        }

        // Insert each first-level key as its own row
        // Skip keys that belong in global.inc.php (file config overrides DB)
        foreach ($data as $key => $value) {
            if ($name === 'config' && isset($glob[$key])) {
                continue;
            }
            $encoded_value = is_array($value) ? json_encode($value) : (string)$value;
            $db->insert('CubeCart_config', array(
                'name'         => $name,
                'config_key'   => $key,
                'config_value' => $encoded_value
            ));
        }
    }
}

// Drop the legacy table
$db->misc("DROP TABLE IF EXISTS `".$glob['dbprefix']."CubeCart_config_legacy`");

// Remove discontinued cache backends (memcache and xcache)
if (isset($glob['cache']) && in_array($glob['cache'], array('memcache', 'xcache'))) {
    $glob['cache'] = 'file';
    $contents = file_get_contents($global_file);
    if ($contents !== false) {
        $updated = preg_replace(
            '/(\$glob\[\'cache\'\]\s*=\s*\')(?:memcache|xcache)(\';)/',
            '${1}file${2}',
            $contents
        );
        if ($updated !== $contents) {
            file_put_contents($global_file, $updated);
        }
    }
}
