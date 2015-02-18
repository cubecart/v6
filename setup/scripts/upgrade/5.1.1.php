<?php
## fix Contact Form departments
$config_upgrade = Config::getInstance($glob);
$contact = $config_upgrade->get('Contact_Form');
if ($contact && isset($contact['department']) && is_array($contact['department'])) {
	array_unshift($contact['department'], array());
	unset ($contact['department'][0]);
	$config_upgrade->set('Contact_Form', '', $contact);
}