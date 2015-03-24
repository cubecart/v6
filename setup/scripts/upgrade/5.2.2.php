<?php
$targets = array(
	array('CubeCart_option_group', 'option_id', 'option_name'),
	array('CubeCart_option_value', 'value_id', 'value_name'),
);

foreach ($targets as $target) {
	if ($rec = $db->select($target[0], array($target[1]), false, array($target[2]=>'ASC'))) {
		$r = 0;
		foreach ($rec as $reco) {
			$db->update($target[0], array('priority'=> ++$r), array($target[1] => $reco[$target[1]]));
		}
	}
}