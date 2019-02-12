<?php
 // Version 6 is now correctly named PHPMailer
if(file_exists(CC_ROOT_DIR.'/classes/phpMailer'))
{
  recursiveDelete(CC_ROOT_DIR.'/classes/phpMailer');
}