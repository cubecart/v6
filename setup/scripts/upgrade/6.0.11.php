<?php
if (!isset($glob['cache'])) {
    $glob['cache'] = 'file';
    writeGlobalConfig($glob, $global_file);
}
