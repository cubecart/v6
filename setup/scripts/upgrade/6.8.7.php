<?php
// #4732 - The Zendesk support widget has gone; upgrades overlay files, so remove its class.
@unlink(CC_ROOT_DIR.'/classes/supportwidget.class.php');
