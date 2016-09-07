<?php
/* Update logos to config instead of file */
$logo_files = glob('../images/logos/*.php');
if ($logo_files) {

    $config_string = $db->select('CubeCart_config', array('array'), array('name' => 'config'));
    $config = json_decode(base64_decode($config_string[0]['array']), true);

    foreach ($logo_files as $file) {

        $handle = fopen($file, "r");
        $contents = fread($handle, filesize($file));
        fclose($handle);

        $logo = str_replace(array('<?php header(\'Location: ', '\', false, 307); ?>', '../'), '', $contents);
        $logo = preg_match('/skins\//', $logo) ? $logo : 'images/logos/' . $logo;

        if (file_exists('../' . $logo)) {
            $basename = str_replace('.php', '', basename($file));
            if (strstr($basename, '-')) {
                $parts = explode('-', $basename);
                $logo_config[$parts[0] . $parts[1]] = $logo;
                if ($config['skin_folder'] == $parts[0] && $config['skin_style'] == $parts[1]) {
                    $other_templates = $logo;
                }
            } else {
                $logo_config[$basename] = $logo;
                if ($config['skin_folder'] == $basename) {
                    $other_templates = $logo;
                }
            }
        }
    }
    if (is_array($logo_config)) {
        $logo_config['emails'] = $other_templates;
        $logo_config['invoices'] = $other_templates;
        $db->insert('CubeCart_config', array('name' => 'logos', 'array' => base64_encode(json_encode($logo_config))));
    }
}