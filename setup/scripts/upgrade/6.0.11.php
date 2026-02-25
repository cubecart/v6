<?php
$cache_defined = false;
foreach ($glob as $key => $value) {
    $value = is_array($value) ? var_export($value, true) : "'".addslashes($value)."'";
    $cfg[] = sprintf("\$glob['%s'] = %s;", $key, $value);
    if ($key == 'cache') {
        $cache_defined = true;
    }
}

if (!$cache_defined) {
    $cache_name = 'file';
    $cfg[] =sprintf("\$glob['cache'] = '%s';", $cache_name);

    $cfg = sprintf("<?php\n%s\n?>", implode("\n", $cfg));
    rename($global_file, $global_file.'-'.date('Ymdgis').'.php');
    file_put_contents($global_file, $cfg);
}
