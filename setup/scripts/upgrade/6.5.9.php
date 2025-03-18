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
$sql_statements = $db->misc("SELECT CONCAT('ALTER TABLE `', table_name, '` ENGINE=InnoDB;') AS sql_statement FROM information_schema.tables AS tb WHERE table_schema = '".$glob['dbdatabase']."' AND `ENGINE` = 'MyISAM' AND `TABLE_TYPE` = 'BASE TABLE' ORDER BY table_name DESC;");
if(is_array($sql_statements) && !empty($sql_statements)) {
    foreach($sql_statements as $s) {
        $GLOBALS['db']->misc($s['sql_statement']);
    }
}