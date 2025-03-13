<?php
$main = glob(CC_ROOT_DIR.'/skins/*/templates/main.php', GLOB_MARK);
if(is_array($main)) {
    foreach($main as $p) {
        $c = file_get_contents($p);
        $find = '{$ACP_WIDGET}';
        if(!strpos($c, $find)) {
            $nc = str_replace('</body>', $find.'</body>', $c);
            file_put_contents($p, $nc);
            unset($c);
        }
    }
}