<?php

$DeveloperEmailAddress = "";



/////////////////////////////////////////////////////////////////////////////////////////
//
//
//                      NOTHING SHOULD BE EDITED BELOW THIS NOTICE
//
//
/////////////////////////////////////////////////////////////////////////////////////////


$PaymentProcessorDomain = "iridiumcorp.net";
$PaymentProcessorPort   = 443;

if ($PaymentProcessorPort == 443) {
    $PaymentProcessorFullDomain = $PaymentProcessorDomain . "/";
} else {
    $PaymentProcessorFullDomain = $PaymentProcessorDomain . ":" . $PaymentProcessorPort . "/";
}
?>